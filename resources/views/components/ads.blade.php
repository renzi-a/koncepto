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
    class="mb-10 relative w-full h-64 rounded-lg overflow-hidden shadow-md"
>
    <template x-for="(slide, index) in slides" :key="index">
        <div 
            x-show="currentSlide === index" 
            class="absolute inset-0 transition-all duration-700"
        >
            <img 
                :src="slide" 
                class="w-full h-full object-cover object-center" 
                alt="Advertisement" 
            />
        </div>
    </template>
</div>
