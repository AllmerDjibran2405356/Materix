const images = [
    "../public/images/login0.png",
    "../public/images/login1.png"
];

let index = 0;
const imageContainer = document.querySelector('.c_login-left');

setInterval(() => {
    index = (index + 1) % images.length;
    imageContainer.style.backgroundImage = `url('${images[index]}')`;
}, 4000); // milisecond