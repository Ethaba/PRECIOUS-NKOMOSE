document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.querySelector('.site-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function() {
            nav.classList.toggle('active');
        });
    }

    var sectionCards = document.querySelectorAll('.section-card');
    var sectionItems = document.querySelectorAll('.section-list-item');
    var preferredInput = document.querySelector('#preferredSection');
    var sectionSelect = document.querySelector('#preferredSectionSelect');
    var guestInput = document.querySelector('#guestCount') || document.querySelector('#guests');
    var notesInput = document.querySelector('#seatNotes') || document.querySelector('#notes');
    var nextButton = document.querySelector('#reserveNext') || document.querySelector('form .primary-action');
    var confirmationBadge = document.querySelector('.confirmation-badge');

    function updateReservationControls() {
        // determine section either from select or hidden input
        var hasSection = (sectionSelect && sectionSelect.value !== '') || (preferredInput && preferredInput.value !== '');
        if (!guestInput || !notesInput || !nextButton) return;
        var hasGuests = guestInput.value !== '';
        var hasNotes = notesInput.value.trim() !== '';
        nextButton.disabled = !(hasSection && hasGuests && hasNotes);
    }

    if (sectionSelect) {
        sectionSelect.addEventListener('change', function() {
            var id = sectionSelect.value;
            if (preferredInput) preferredInput.value = id;
            updateReservationControls();
            highlightSection(id);
        });
    }

    if (guestInput) guestInput.addEventListener('input', updateReservationControls);
    if (notesInput) notesInput.addEventListener('input', updateReservationControls);

    sectionItems.forEach(function(item) {
        item.addEventListener('click', function() {
            var id = item.dataset.sectionId;
            if (sectionSelect) sectionSelect.value = id;
            if (preferredInput) preferredInput.value = id;
            highlightSection(id);
            updateReservationControls();
            item.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });

    sectionCards.forEach(function(card) {
        card.addEventListener('click', function() {
            var id = card.dataset.sectionId;
            if (!id) return;
            if (sectionSelect) sectionSelect.value = id;
            if (preferredInput) preferredInput.value = id;
            highlightSection(id);
            updateReservationControls();
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });

    function highlightSection(id) {
        sectionCards.forEach(function(card) {
            card.classList.toggle('active', card.dataset.sectionId === id);
        });
        sectionItems.forEach(function(item) {
            item.classList.toggle('active', item.dataset.sectionId === id);
        });
    }

    if (sectionItems.length > 0 && document.querySelector('.scroll-list')) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var id = entry.target.dataset.sectionId;
                    if (sectionSelect) sectionSelect.value = id;
                    if (preferredInput) preferredInput.value = id;
                    highlightSection(id);
                    updateReservationControls();
                }
            });
        }, { root: document.querySelector('.scroll-list'), threshold: 0.65 });

        sectionItems.forEach(function(item) {
            observer.observe(item);
        });
    }

    if (confirmationBadge) {
        confirmationBadge.animate([
            { transform: 'scale(0.88)', opacity: 0 },
            { transform: 'scale(1)', opacity: 1 }
        ], { duration: 600, easing: 'ease-out' });
    }
});
