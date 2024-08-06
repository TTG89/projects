<template>
    <div class="loader"></div>
</template>

<script>
export default {
    name: 'pageLoader',
    props: {
        loading: {
            type: Boolean,
            default: false,
        },
        loaderStyle: {
            type: Object,
            default: () => ({}),
        },
    },
};
</script>

<style scoped>
@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.loader {
    border: 5px solid #f3f3f3;
    border-top: 5px solid #253646;
    border-radius: 50%;
    width: 70px;
    height: 70px;
    animation: spin .8s linear infinite;
    display: block !important;
    margin: 100px auto;
}
</style>