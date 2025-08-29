import Alpine from 'alpinejs'
import { gsap } from 'gsap'
import {Draggable} from 'gsap/Draggable'

const DialRotation = () => {
    Alpine.data('dialRotation', () => ({
        imageURLs: [] as string[],
        init() {
            gsap.registerPlugin(Draggable)
            this.$nextTick(() => {
                let str = this.$el.dataset.assets?.replace(/,$/, '')
                this.imageURLs = str ? str.split(',') : []
                const circle = this.$refs.mainCircle
                const images = this.placeImages(this.imageURLs, circle)
                const spin = gsap.timeline({repeat: -1, defaults: { duration: 50, ease: "none"}})
                    .to(circle, {rotation: 360})
                    .to(images, {rotation: -360}, 0)

                this.drapable(circle, spin)
            })
        },
        placeImages(imageURLs: string[], circle: HTMLElement) {
            let angleIncrement = Math.PI * 2 / imageURLs.length,
                radius = circle.offsetWidth / 2,
                images = [],
                image, angle, i
            for (i = 0; i < imageURLs.length; i ++) {
                image = new Image();
                images.push(image);
                circle.appendChild(image);
                angle = angleIncrement * i;
                gsap.set(image, {
                    attr:{ src: imageURLs[i] },
                    position:"absolute", 
                    top:0, 
                    left:0, 
                    xPercent:-50, 
                    yPercent:-50,
                    transformOrigin:"50% 50%",
                    x: radius + Math.cos(angle) * radius,
                    y: radius + Math.sin(angle) * radius
                });
            }
            return images;
        },

        drapable(circle: HTMLElement, spin: any) {
            Draggable.create(circle, {
                type: 'rotation',
                inertia: true,
                onPressInit: () => spin.pause(),
                onDrag: () => {
                    let angle = gsap.getProperty(circle, "rotation") as number
                    spin.progress((angle % 360) / 360)
                },
                onThrowComplete: () => {
                    spin.resume()
                    gsap.fromTo(spin, {timeScale: 0}, {duration: 1, timeScale: 1, ease: 'power1.in'})
                }
            })
        },

    }))
}

export default DialRotation