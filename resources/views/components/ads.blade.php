<div
    x-data="{
        currentSlide: 0,
        slides: {{ json_encode([
            asset('images/ads/ads1.jpeg'),
            asset('images/ads/ads2.jpeg'),
            asset('images/ads/ads3.jpeg'),
        ]) }},
        init() {
            setInterval(() => {
                this.currentSlide = (this.currentSlide + 1) % this.slides.length;
            }, 4000);
        }
    }"
    class="mb-10 relative w-full h-80 rounded-lg overflow-hidden shadow-md"
>
    <template x-for="(slide, index) in slides" :key="index">
        <div
            x-show="currentSlide === index"
            x-transition:enter="transform translate-x-full"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform translate-x-0"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="absolute inset-0 transition-transform duration-500 ease-in-out"
        >
            <img
                :src="slide"
                class="w-full h-full object-cover" 
                alt="Advertisement"
            />
        </div>
    </template>
</div>