// MediConnect Main JavaScript

// Alert auto-dismiss
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(a => {
        setTimeout(() => {
            a.style.transition = 'opacity 0.5s';
            a.style.opacity = '0';
            setTimeout(() => a.remove(), 500);
        }, 4000);
    });

    // Appointment time slots
    document.querySelectorAll('.appointment-slot').forEach(slot => {
        slot.addEventListener('click', function () {
            document.querySelectorAll('.appointment-slot').forEach(s => s.classList.remove('selected'));
            this.classList.add('selected');
            const input = document.getElementById('appointment_time');
            if (input) input.value = this.dataset.time;
        });
    });

    // Symptom checker
    const symptomBtn = document.getElementById('symptomCheckBtn');
    if (symptomBtn) {
        symptomBtn.addEventListener('click', function () {
            const symptoms = document.getElementById('symptoms').value;
            if (!symptoms.trim()) {
                showAlert('Please describe your symptoms.', 'warning');
                return;
            }
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing...';
            this.disabled = true;
            setTimeout(() => {
                window.location.href = 'doctors.php?symptoms=' + encodeURIComponent(symptoms);
            }, 1000);
        });
    }
});

function showAlert(msg, type = 'info') {
    const div = document.createElement('div');
    div.className = `alert alert-${type}`;
    div.innerHTML = `<i class="fas fa-info-circle"></i> ${msg}`;
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(div, container.firstChild);
    setTimeout(() => div.remove(), 4000);
}

// Preview image before upload
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById(previewId);
            if (img) { img.src = e.target.result; img.style.display = 'block'; }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Print prescription
function printPrescription() {
    window.print();
}
