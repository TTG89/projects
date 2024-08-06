<!-- ProductImage.vue -->
<template>
  <div v-if="hasImages" class="productImageContainer">
    <div class="carousel">
      <div class="carousel-inner" :style="{ transform: `translateX(-${currentIndex * 100}%)` }">
        <div v-for="(image, index) in images" :key="index" class="carousel-item">
          <img :src="image" :alt="`Product Image ${index + 1}`" class="productImage" loading="lazy" />
        </div>
      </div>
      <button class="carousel-control-prev" @click="prevImage">&#10094;</button>
      <button class="carousel-control-next" @click="nextImage">&#10095;</button>
    </div>
    <div class="thumbnails">
      <div v-for="(image, index) in images" :key="index" class="thumbnail" @click="setCurrentIndex(index)" :class="{ active: currentIndex === index }">
        <img :src="image" :alt="`Thumbnail ${index + 1}`" class="thumbnailImage" loading="lazy" />
      </div>
    </div>
    <div class="slide-counter">
      {{ currentIndex + 1 }} / {{ totalSlides }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    images: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      currentIndex: 0,
    };
  },
  computed: {
  totalSlides() {
    return this.images.length;
  },
  hasImages() {
    return Array.isArray(this.images) && this.images.length > 0;
  }
},
  methods: {
    nextImage() {
      if (this.hasImages) {
        this.currentIndex = (this.currentIndex + 1) % this.images.length;
      }
    },
    prevImage() {
      if (this.hasImages) {
        this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
      }
    },
    setCurrentIndex(index) {
      this.currentIndex = index;
    },
  },  
};
</script>

<style scoped>
.productImageContainer {
  width: 100%;
  position: relative;
  margin-bottom:30px;
}

.carousel {
  position: relative;
  overflow: hidden;
}

.carousel-inner {
  display: flex;
  transition: transform 0.5s ease-in-out;
}

.carousel-item {
  min-width: 100%;
}

.productImage {
  width: 100%;
  height: auto;
}

.carousel-control-prev,
.carousel-control-next {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background-color:#fff;
  border: 1px solid #DCDFE1;
  color: #000;
  padding: 10px 15px;
  cursor: pointer;
}

.carousel-control-prev {
  left: 10px;
}

.carousel-control-next {
  right: 10px;
}

.thumbnails {
  display: flex;
  justify-content: flex-start;
  margin-top: 10px;
}

.thumbnail {
  margin: 0 5px;
  cursor: pointer;
  height: 70px;
}

.thumbnail.active {
  border-bottom: 3px solid #31708F;
}

.thumbnailImage {
  width: 50px;
  height: auto;
}

.slide-counter {
  position: absolute;
  bottom: 12%;
  right: 10px;
  background-color: #fff;
  color: #000;
  padding: 5px 12px;
  font-size: 14px;
  border: 2px solid #DCDFE1;
}
</style>
