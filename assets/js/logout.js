// Script For logout page

let seconds = 5;
const countdown = document.getElementById('countdown');

setInterval(() => {
    if (seconds > 0) {
        seconds--;
    }
    countdown.innerText = seconds;
}, 1000)