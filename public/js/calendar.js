document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('overlay');
    const bottomSheet = document.getElementById('bottomSheet');
    const sheetTitle = document.getElementById('sheetTitle');
    const sheetDate = document.getElementById('sheetDate');
    const sheetContent = document.getElementById('sheetContent');
    const sheetClose = document.getElementById('sheetClose');

    // Open sheet when clicking on diary day
    document.querySelectorAll('.day.has-diary').forEach(function(el) {
        el.addEventListener('click', function() {
            sheetTitle.textContent = this.dataset.title;
            sheetDate.textContent = this.dataset.date;
            sheetContent.textContent = this.dataset.content;
            overlay.classList.add('active');
            bottomSheet.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    });

    // Close sheet
    function closeSheet() {
        overlay.classList.remove('active');
        bottomSheet.classList.remove('active');
        document.body.style.overflow = '';
    }

    overlay.addEventListener('click', closeSheet);
    sheetClose.addEventListener('click', closeSheet);

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSheet();
    });

    // Swipe navigation
    var touchStartX = 0;
    var touchEndX = 0;
    var calendar = document.querySelector('.calendar');

    if (calendar) {
        calendar.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });

        calendar.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            var diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) {
                    document.querySelector('.nav a:last-child').click();
                } else {
                    document.querySelector('.nav a:first-child').click();
                }
            }
        });
    }
});
