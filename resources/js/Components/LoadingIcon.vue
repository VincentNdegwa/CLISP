<template>
    <div class="lds-facebook" bis_skin_checked="1">
        <div bis_skin_checked="1"></div>
        <div bis_skin_checked="1"></div>
        <div bis_skin_checked="1"></div>
    </div>
</template>

<style>
.lds-facebook,
.lds-facebook div {
    box-sizing: border-box;
}
.lds-facebook {
    display: inline-block;
    position: relative;
    width: 70px;
    height: 70px;
}
.lds-facebook div {
    display: inline-block;
    position: absolute;
    left: 8px;
    width: 16px;
    background: currentColor;
    animation: lds-facebook 1.2s cubic-bezier(0, 0.5, 0.5, 1) infinite;
}
.lds-facebook div:nth-child(1) {
    left: 8px;
    animation-delay: -0.24s;
}
.lds-facebook div:nth-child(2) {
    left: 32px;
    animation-delay: -0.12s;
}
.lds-facebook div:nth-child(3) {
    left: 56px;
    animation-delay: 0s;
}
@keyframes lds-facebook {
    0% {
        top: 8px;
        height: 64px;
    }
    50%,
    100% {
        top: 24px;
        height: 32px;
    }
}
</style>
