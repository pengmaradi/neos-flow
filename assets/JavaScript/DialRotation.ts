import Alpine from 'alpinejs'
import { gsap } from 'gsap'
import { Draggable } from 'gsap/Draggable'

const DialRotation = () => {
    Alpine.data('dialRotation', () => ({
        imageURLs: [] as string[],
        draggableInstance: null as any,
        spin: null as gsap.core.Timeline | null,
        init() {
            gsap.registerPlugin(Draggable)
            this.$nextTick(() => {
                let str = this.$el.dataset.assets?.replace(/,$/, '')
                this.imageURLs = str ? str.split(',') : []
                const circle = this.$refs.mainCircle
                const images = this.placeImages(this.imageURLs, circle)

                images.forEach((item) => {

                    item.addEventListener('click', () => {
                        images.forEach(img => {
                            img.style.zIndex = '0';
                        })
                        item.style.zIndex = '1'
                    })
                })

                this.spin = gsap.timeline({
                        repeat: -1, 
                        defaults: { duration: 40, ease: "none" }
                    })
                    .to(circle, { rotation: 360 })
                    .to(images, { rotation: -360 }, 0)
                
                Draggable.create(circle, {
                    type: 'rotation',
                    inertia: true,
                    onPressInit: () => {
                        this.spin?.pause()
                    },

                    onDrag: () => {
                        let rotation = gsap.getProperty(circle, "rotation") as number
                        let angle = (rotation + 360) % 360
                        this.spin?.progress(angle/360).play()
                    }

                })
                
            })
        },
        placeImages(imageURLs: string[], circle: HTMLElement) {
            let angleIncrement: number = Math.PI * 2 / imageURLs.length,
                radius: number = circle.offsetWidth / 2,
                images = [],
                image, angle: number
            
            for (let i = 0; i < imageURLs.length; i++) {
                image = new Image();
                images.push(image);
                circle.appendChild(image);
                angle = angleIncrement * i;
                
                gsap.set(image, {
                    attr: { src: imageURLs[i] },
                    xPercent: -50,
                    yPercent: -50,
                    transformOrigin: "50% 50%",
                    x: radius + Math.cos(angle) * radius,
                    y: radius + Math.sin(angle) * radius,
                });
            }
            return images;
        },
    }))
}

export default DialRotation