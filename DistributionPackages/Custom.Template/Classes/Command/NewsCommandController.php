<?php
namespace Custom\Template\Command;

/*
 * This file is part of the Custom.Template package.
 */

use GuzzleHttp\Client;

use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\ContentRepository\Core\DimensionSpace\DimensionSpacePoint;
use Neos\ContentRepository\Core\DimensionSpace\OriginDimensionSpacePoint;
use Neos\ContentRepository\Core\Feature\NodeModification\Dto\PropertyValuesToWrite;
use Neos\ContentRepository\Core\NodeType\NodeTypeName;
use Neos\ContentRepository\Core\Feature\NodeCreation\Command\CreateNodeAggregateWithNode;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\ContentRepository;
use Neos\ContentRepository\Core\Feature\NodeRemoval\Command\RemoveNodeAggregate;
use Neos\ContentRepository\Core\Projection\ContentGraph\NodeAggregate;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindChildNodesFilter;
use Neos\ContentRepository\Core\SharedModel\Node\NodeName;
use Neos\ContentRepository\Core\SharedModel\Node\NodeVariantSelectionStrategy;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\ResourceManagement\ResourceRepository;
use Neos\Media\Domain\Model\Image;
use Neos\Media\Domain\Model\Asset;
use Neos\Media\Domain\Service\AssetService;

#[Flow\Scope("singleton")]
final class NewsCommandController extends CommandController
{
    const NEWS_API = 'https://newsapi.org/v2/everything?q=%s&language=%s&sortBy=publishedAt&apiKey=%s';
    
    protected Client $client;

    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    #[Flow\Inject]
    protected ResourceManager $resourceManager;

    #[Flow\Inject]
    protected PersistenceManager $persistenceManager;

    #[Flow\Inject]
    protected AssetService $assetService;

    #[Flow\Inject]
    protected ResourceRepository $resourceRepository;

    /**
     * 
     * run `ddev flow news:newsimport Switzerland de --dryRun=true` `ddev flow news:newsimport China zh`
     * 
     */
    public function newsImportCommand(string $country, string $language, bool $dryRun = false): void
    {
        if ($dryRun) $this->outputLine('<info>DRY RUN MODE - No nodes will be created</info>');

        try {
            $this->createNewsNode($country, $language, $dryRun);
        } catch (\Exception $e) {
            $this->outputLine('<error>Import failed: ' . $e->getMessage() . '</error>');
            $this->outputLine('<error>Stack trace: ' . $e->getTraceAsString() . '</error>');
            $this->quit(1);
        }
    }

    private function getChildNodeByName(string $nodeName = 'Custom.Template:Document.NewsList', $parentNode = null, $all = false): NodeAggregate|array
    {
        $contentRepositoryId = ContentRepositoryId::fromString('default');
        $contentRepository = $this->contentRepositoryRegistry->get($contentRepositoryId);
        $workspaceName = WorkspaceName::forLive();
        $contentGraph = $contentRepository->getContentGraph($workspaceName);
        $nodes = [];
        
        $parentNode = $parentNode ?? $this->getSiteNode($contentRepository, $workspaceName);
        
        $rootChildrenNodes = $contentGraph->findChildNodeAggregates(
                $parentNode->nodeAggregateId,
                NodeTypeName::fromString($nodeName)
        );
        foreach ($rootChildrenNodes as $k => $childNode) {
            if ($childNode->nodeTypeName->value === $nodeName) {
                if ($all) {
                    $nodes[$k] = $childNode;
                } else {
                    return $childNode;
                }
            }
        }

        return $nodes;
        throw new \Exception('未找到节点' . $nodeName);
    }

    private function getSiteNode(
        ContentRepository $contentRepository, 
        WorkspaceName $workspaceName
    ): NodeAggregate {
        $contentGraph = $contentRepository->getContentGraph($workspaceName);
        $rootNodeAggregate = $contentGraph->findRootNodeAggregateByType(
            NodeTypeName::fromString('Neos.Neos:Sites')
        );
        if ($rootNodeAggregate === null) {
            throw new \Exception('未找到Sites根节点');
        }
        $siteNodes = $contentGraph->findChildNodeAggregates(
            $rootNodeAggregate->nodeAggregateId,
            FindChildNodesFilter::create()
        );
        
        foreach ($siteNodes as $siteNode) {
            return $siteNode;
        }
        
        throw new \Exception('未找到站点节点');
    }

    private function createNewsNode(string $country, string $language, bool $dryRun = false): bool 
    {

        $contentRepositoryId = ContentRepositoryId::fromString('default');
        // 1. 使用 ContentRepositoryRegistry 获取 ContentRepository
        $contentRepository = $this->contentRepositoryRegistry->get($contentRepositoryId);
        // 2. 获取 live workspace
        $workspaceName = WorkspaceName::forLive();

        $dimensionSpacePoint = DimensionSpacePoint::fromArray(['language' => $language]);
        $originDimensionSpacePoint = OriginDimensionSpacePoint::fromDimensionSpacePoint($dimensionSpacePoint);
        $newsNodeName = NodeTypeName::fromString('Custom.Template:Document.News');

        $imported = 0;
        $newsListNode = $this->getChildNodeByName();
        $this->removeOldNews($newsListNode, $contentRepository, $language);
        die(' on line ' . __LINE__);
        $newsData = $this->fetchNews($country, $language);

        if ($newsData['totalResults']) {
                foreach ($newsData['articles'] as $event) {
                    $nodeAggregateId = NodeAggregateId::create();
                    $title = substr($event['title'], 0, 50);
                    if ($language == 'zh') {
                        $title = $event['title'];
                    }
                    $uriName = NodeName::fromString($this->generateNodeName($title));

                    $propertyValues = PropertyValuesToWrite::fromArray([
                        'source_name' => $event['source']['name'],
                        'author' => $event['author'],
                        'title' => $title,
                        'description' => $event['description'],
                        'url' => $event['url'],
                        'image' => $event['urlToImage'] ? $this->downloadAndCreateImageAsset($event['urlToImage'], $title) : null,
                        'date' => new \DateTimeImmutable($event['publishedAt']) ?? new \DateTimeImmutable(),
                        'uriPathSegment' => (string)$uriName ?? uniqid('news-'),
                    ]);

                    $command = CreateNodeAggregateWithNode::create(
                        $workspaceName, // WorkspaceName
                        $nodeAggregateId, // for new node nodeAggregateId
                        $newsNodeName, // NodeTypeName
                        $originDimensionSpacePoint, // languages
                        $newsListNode->nodeAggregateId, //should be Newslist page nodeAggregateId
                        null,
                        $propertyValues, // PropertyValuesToWrite
                        // NodeReferencesToWrite $references = null
                    );
        
                    $imported ++;

                    if (!$dryRun) {
                        $contentRepository->handle($command);
                        $this->persistenceManager->persistAll();
                        $this->outputLine(sprintf('<success>imported %d news: %s</success>', $imported, $title));
                    } else {
                        $this->outputLine(sprintf('<info>Would import %d news: %s</info>', $imported, $uriName));
                    }
                }
            }


        return true;
    }

    private function downloadAndCreateImageAsset(string $urlToImage, $title = ''): ?Asset
    {
        try {
            $this->outputLine('Downloading image: ' . $urlToImage);
            $options = [
                'timeout' => 30,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'image/webp,image/apng,image/*,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Connection' => 'keep-alive',
                    'Upgrade-Insecure-Requests' => '1'
                ],
                'verify' => false, // Skip SSL verification for problematic sites
                'allow_redirects' => true
            ];

            $this->client = new Client($options);

            $response = $this->client->get($urlToImage);
            $imageData = $response->getBody()->getContents();
            
            $imageInfo = getimagesizefromstring($imageData);
            if ($imageInfo === false) {
                $this->outputLine('✗ Invalid image data');
                return null;
            }

            $this->outputLine(sprintf('✓ Downloaded %s (%dx%d, %s)', 
            $this->formatBytes(strlen($imageData)),
                $imageInfo[0], 
                $imageInfo[1], 
                $imageInfo['mime']
            ));
        
            $contentType = $response->getHeader('Content-Type')[0] ?? $imageInfo['mime'] ?? 'image/jpeg';
            $extension = $this->getExtensionFromContentType($contentType);
            $filename = $this->generateShortNodeAggregateId($title) . '.' . $extension;

            $tempFile = tmpfile();
            if (!$tempFile) {
                $this->outputLine('✗ Could not create temporary file');
                return null;
            }

            fwrite($tempFile, $imageData);
            $tempPath = stream_get_meta_data($tempFile)['uri'];
            
            try {
                $resource = $this->resourceManager->importResource($tempPath, 'persistent', $filename);
            } catch (\Exception $e1) {
                try {
                        // Last fallback: use importResourceFromContent
                        $resource = $this->resourceManager->importResourceFromContent($imageData, '', $filename);
                    } catch (\Exception $e2) {
                        try {
                            $resource = $this->resourceManager->importResourceFromContent($imageData, $filename);
                        } catch (\Exception $e3) {

                        }
                        fclose($tempFile);
                        $this->outputLine('    ✗ Failed to import resource with any method');
                        $this->outputLine('    Error 1: ' . $e1->getMessage());
                        $this->outputLine('    Error 2: ' . $e2->getMessage());
                        $this->outputLine('    Error 3: ' . $e3->getMessage());
                        return null;
                    }
            }
            
            if (!$resource instanceof \Neos\Flow\ResourceManagement\PersistentResource) {
                fclose($tempFile);
                $this->outputLine('✗ Failed to create resource');
                return null;
            }
            $asset = new Image($resource);
            $asset->setTitle($title);
            $asset->setCaption('Imported of ' . $title);
            $this->assetService->getRepository($asset)->add($asset);

            fclose($tempFile);
            $this->outputLine('✓ Created image asset: ' . $filename);
            
            return $asset;

        } catch (\Exception $e) {
            $this->outputLine('<error>✗ Empty image data received: ' . $e->getMessage() . '</error>');
            return null;
        }
    }

    private function generateShortNodeAggregateId(string $title): NodeAggregateId
    {
        // Create a short, unique identifier based on title and timestamp
        $shortTitle = $this->generateNodeName($title);
        $shortTitle = substr($shortTitle, 0, 10); // Limit title part
        
        // Add timestamp for uniqueness (shorter than full UUID)
        //$timestamp = date('ymd-His'); // YY-MM-DD-HH-ii-ss format
        
        // Create a shorter hash for additional uniqueness
        $hash = substr(md5($title . microtime(true)), 0, 3);
        
        // Combine: news-{shorttitle}-{timestamp}-{hash}
        $identifier = sprintf('%s-%s', $shortTitle, $hash);
        
        // Ensure it's not too long (max 64 chars typically)
        $identifier = substr($identifier, 0, 30);
        $identifier = str_replace('--', '-', $identifier);
        
        return NodeAggregateId::fromString($identifier);
    }

    private function getExtensionFromContentType(string $contentType): string
    {
        // Clean content type (remove charset etc.)
        $contentType = strtolower(explode(';', $contentType)[0]);
        
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg', 
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'image/bmp' => 'bmp',
            'image/tiff' => 'tiff'
        ];

        return $extensions[$contentType] ?? 'jpg';
    }

    private function formatBytes(int $size): string
    {
        if ($size >= 1048576) {
            return round($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return round($size / 1024, 2) . ' KB';
        } else {
            return $size . ' bytes';
        }
    }

    private function removeOldNews($newsListNode, $contentRepository, string $language)
    {
        $newsNodeName = NodeTypeName::fromString('Custom.Template:Document.News');
        $workspaceName = WorkspaceName::forLive();
        $existingNews = $this->getChildNodeByName($newsNodeName,  $newsListNode, true);
        $newsCount = count($existingNews);
        $num = 0;
        if ($newsCount) {
            foreach ($existingNews as $newsNode) {
                $removeCommand = RemoveNodeAggregate::create(
                    $workspaceName,
                    $newsNode->nodeAggregateId,
                    DimensionSpacePoint::fromArray(['language' => $language]),
                    NodeVariantSelectionStrategy::STRATEGY_ALL_VARIANTS
                );
                var_dump($removeCommand);
                die(__LINE__);
                $contentRepository->handle($removeCommand);
                $num ++;
                $this->outputLine(sprintf('<info>removed news: %s - %d / %d</info>', $newsNode->nodeAggregateId, $num, $newsCount));
            }
        }
    }

    private function fetchNews(string $country, string $language): mixed
    {
        $this->client = new Client([
            'timeout' => 0,
            'headers' => [
                'Connection' => 'Keep-Alive',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json; charset=utf-8',
            ],
        ]);

        // 1. get .env news api key
        $key = $_ENV['NEWS_API'];
        if (!strlen($key)) {
            throw new \Exception('未找到news API key');
        }

        $apiUrl = sprintf(self::NEWS_API, $country, $language, $key);

        if (!filter_var($apiUrl, FILTER_SANITIZE_URL)) {
            $this->outputLine("<error>Invalid news API</error>");
            die;
        }

        $response = $this->client->get($apiUrl);

        if ($response->getStatusCode() !== 200) {
            throw new \InvalidArgumentException('Invalid XML URL' . $apiUrl, 1754634271);
        }

        $content = $response->getBody()->getContents();
        return json_decode($content, true);
    }

    private function generateNodeName(string $title): string
    {
        $nodeName = $this->transliterateChinese($title);
        $nodeName = strtolower($nodeName);
        $nodeName = preg_replace('/[^a-z0-9\-]/', '-', strtolower($nodeName));
        $nodeName = preg_replace('/-+/', '-', $nodeName);
        $nodeName = trim($nodeName, '-');
        $nodeName = substr($nodeName, 0, 50);

        if (!preg_match('/^[a-z0-9]/', $nodeName)) {
            $nodeName = 'news-' . $nodeName;
        }

        if (strlen($nodeName) < 3) {
            $nodeName = 'news-' . date('Y-m-d-H-i-s');
        }
        
        return $nodeName;
    }

    private function transliterateChinese(string $text): string
    {
        // Method 1: Use PHP's transliterator if available (requires intl extension)
        if (class_exists('Transliterator')) {
            $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
            if ($transliterator) {
                $result = $transliterator->transliterate($text);
                if ($result !== false) {
                    return $result;
                }
            }
        }

        // Method 2: Manual mapping for common Chinese characters (fallback)
        $chineseToAscii = [
            '新闻' => 'news',
            '科技' => 'technology',
            '商业' => 'business',
            '体育' => 'sports',
            '娱乐' => 'entertainment',
            '健康' => 'health',
            '教育' => 'education',
            '财经' => 'finance',
            '汽车' => 'automotive',
            '房产' => 'realestate',
            '文章' => 'article',
            '报道' => 'report',
            '消息' => 'message',
            // Add more mappings as needed
        ];

        $text = str_replace(array_keys($chineseToAscii), array_values($chineseToAscii), $text);

        // Method 3: If still contains non-ASCII, URL encode or use timestamp
        if (!mb_check_encoding($text, 'ASCII')) {
            // Option A: Use URL encoding (less readable)
            // return rawurlencode($text);
            
            // Option B: Generate from timestamp and random string (more readable)
            return 'news-' . date('Y-m-d') . '-' . substr(md5($text), 0, 6);
        }

        return $text;
    }
}
