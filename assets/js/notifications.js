const bell = document.getElementById('notification');
bell.addEventListener('click', function() {
    const overlay = document.getElementById('overlay-notification')
    // const main = document
    // main.style.filter = 'brightness(0.8)'
    overlay.style.display = 'flex'
    // overlay.style.filter = 'brightness(1)'
})

const cross = document.getElementById('cross')
cross.addEventListener('click', function() {
    const overlay = document.getElementById('overlay-notification')
    overlay.style.display = 'none'
})