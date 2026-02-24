## build project frontend:
- run first `npm ci && npm run build`
- watch with serve: `npm run watch-serve` - settings watchServe: true

# fusion 元属性总结表

|元属性 | 用途 | 示例 |
|-|-|-|
|@if| 条件渲染| @if = ${condition}|
|@context| 定义局部变量| @context { var = value }|
|@process| 数据处理管道,按顺序执行多个处理器| myValue = " Hello World " myValue.@process { // 按顺序处理：修剪 → 大写 → 添加前缀 trim = ${value.trim()} uppercase = ${value.toUpperCase()}  addPrefix = ${'Greeting: ' + value} }|
|@apply| 混合继承| `@apply.spread_1 = ${expression}`|
|@position| 渲染顺序| @position = 'start'|
|@cache |缓存控制| @cache { mode = 'cached' }|
|@ignoreProperties| 忽略对象| @ignore = true|
|@key|定义数组项的属性名称|多个子元素时的命名,只支持字符串值，不支持表达式, 未设置时使用 `index_x`（从 x=1 开始）|
|@glue| 数组连接符| @glue = ', '|
|@path| 将子元素渲染到指定的 Fusion 路径|避免将内容放入默认的 content属性,只支持字符串值 `<h2 @path="title">{props.title}</h2>`|
|@children|指定接收子内容的属性名称|自定义子元素的注入位置 `<div @children="bodyContent">...</div>`|

这些元属性使得 Neos Fusion 非常强大和灵活，能够处理复杂的渲染逻辑、性能优化和代码组织需求。

### Arbitrary Value Syntax

`lg:[&:hover>ul]:block [&.current>a]:text-red-500 [&.active>a]:text-red-500`


## 什么是SVG Path？
### 移动命令 M x y - 移动到指定坐标
### 直线命令

```
L x y - 绘制直线到指定坐标
H x - 绘制水平线
V y - 绘制垂直线
```

### 曲线命令

```
C x1 y1, x2 y2, x y - 三次贝塞尔曲线
Q x1 y1, x y - 二次贝塞尔曲线
```
### 弧线命令

`A rx ry x-axis-rotation large-arc-flag sweep-flag x y - 椭圆弧线`

### 闭合命令 Z - 闭合路径