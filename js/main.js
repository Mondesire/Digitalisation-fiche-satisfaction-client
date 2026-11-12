document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.stars').forEach(function (group) {
        var inputs = group.querySelectorAll('input[type="radio"]');
        var labels = group.querySelectorAll('.star');

        labels.forEach(function (label, index) {
            label.addEventListener('mouseenter', function () {
                labels.forEach(function (l, i) {
                    l.style.color = i <= index ? '#f59e0b' : '#d1d5db';
                });
            });

            label.addEventListener('mouseleave', function () {
                labels.forEach(function (l, i) {
                    var radio = inputs[i];
                    l.style.color = radio && radio.checked ? '#f59e0b' : '#d1d5db';
                });
            });
        });
    });

    var form = document.getElementById('evaluationForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            var required = ['note_service', 'note_qualite', 'note_prix', 'note_globale'];
            var missing = false;

            required.forEach(function (name) {
                var checked = form.querySelector('input[name="' + name + '"]:checked');
                if (!checked) {
                    missing = true;
                }
            });

            var nom = form.querySelector('#nom');
            if (!nom || nom.value.trim() === '') {
                missing = true;
            }

            if (missing) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires.');
            }
        });
    }

    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.4s';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 400);
        }, 4000);
    });

});
