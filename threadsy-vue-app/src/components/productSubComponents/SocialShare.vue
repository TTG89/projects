<template>
    <div class="socialShare">
      <p>SHARE:</p>
      <a class="socialLink" :href="getSocialShareLink('twitter')" target="_blank" aria-label="Share on Twitter">
        <font-awesome-icon :icon="['fab', 'twitter']" />
      </a>
      <a class="socialLink" :href="getSocialShareLink('facebook')" target="_blank" aria-label="Share on Facebook">
        <font-awesome-icon :icon="['fab', 'facebook']" />
      </a>
      <a class="socialLink" :href="getSocialShareLink('pinterest')" target="_blank" aria-label="Share on Pinterest">
        <font-awesome-icon :icon="['fab', 'pinterest']" />
      </a>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      getSocialShareLink: Function,
    },
  };
  </script>
  
  <style scoped>
  .socialShare {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
  }
  
  .socialShare p {
    font-size: 18px;
    font-weight: bold;
  }
  
  .socialLink svg {
    width: 30px;
    height: 30px;
    padding: 10px;
    color: #253646;
    transition: transform 0.2s;
  }
  
  .socialLink:hover svg {
    transform: scale(1.1);
  }
  </style>
  