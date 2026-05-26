// MediConnect - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {

    // ---- Scroll to top ----
    const scrollBtn = document.createElement('div');
    scrollBtn.className = 'scroll-top';
    scrollBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
    document.body.appendChild(scrollBtn);

    window.addEventListener('scroll', () => {
        scrollBtn.classList.toggle('visible', window.scrollY > 400);
    });
    scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

    // ---- Auto-dismiss alerts ----
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(a => {
            if (!a.classList.contains('alert-keep')) {
                a.style.transition = 'opacity 0.5s';
                a.style.opacity = '0';
                setTimeout(() => a.remove(), 500);
            }
        });
    }, 4000);

    // ---- Star rating input ----
    const starInputs = document.querySelectorAll('.star-input input');
    starInputs.forEach(inp => {
        inp.addEventListener('change', () => {
            const val = document.getElementById('rating_display');
            if (val) val.textContent = inp.value + ' / 5';
        });
    });

    // ---- Appointment time slots ----
    const dateInput = document.getElementById('appointment_date');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
    }

    // ---- Confirm delete actions ----
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', e => {
            if (!confirm(btn.dataset.confirm)) e.preventDefault();
        });
    });

    // ---- AI Chat Widget ----
    const chatBtn = document.querySelector('.ai-chat-btn');
    const chatPanel = document.querySelector('.ai-chat-panel');

    if (chatBtn && chatPanel) {
        chatBtn.addEventListener('click', () => chatPanel.classList.toggle('open'));

        const chatForm = document.getElementById('ai-chat-form');
        if (chatForm) {
            chatForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const input = document.getElementById('ai-chat-input-field');
                const msg = input.value.trim();
                if (!msg) return;

                appendMessage(msg, 'user');
                input.value = '';

                // Simulate AI response (symptom-based recommendations)
                const response = getAIResponse(msg);
                setTimeout(() => appendMessage(response, 'bot'), 800);
            });
        }
    }

    function appendMessage(text, type) {
        const messages = document.getElementById('ai-chat-messages');
        if (!messages) return;
        const div = document.createElement('div');
        div.className = `ai-msg ${type}`;
        div.innerHTML = `<div class="msg-bubble">${text}</div>`;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    function getAIResponse(symptom) {
        const s = symptom.toLowerCase();
        const map = {
            'chest pain': '❤️ Chest pain may indicate a cardiac issue. I recommend seeing a <strong>Cardiologist</strong> immediately. <a href="doctors.php?spec=1">Find Cardiologists</a>',
            'heart': '❤️ For heart-related symptoms, please consult a <strong>Cardiologist</strong>. <a href="doctors.php?spec=1">Find Cardiologists</a>',
            'headache': '🧠 Persistent headaches may require a <strong>Neurologist</strong>. <a href="doctors.php?spec=2">Find Neurologists</a>',
            'fever': '🌡️ For fever and general illness, see a <strong>General Physician</strong>. <a href="doctors.php?spec=9">Find GPs</a>',
            'skin': '🩺 Skin conditions are treated by a <strong>Dermatologist</strong>. <a href="doctors.php?spec=5">Find Dermatologists</a>',
            'bone': '🦴 Bone or joint pain — consult an <strong>Orthopedic Surgeon</strong>. <a href="doctors.php?spec=3">Find Orthopedics</a>',
            'joint': '🦴 Joint pain — consult an <strong>Orthopedic Surgeon</strong>. <a href="doctors.php?spec=3">Find Orthopedics</a>',
            'child': '👶 For children\'s health, consult a <strong>Pediatrician</strong>. <a href="doctors.php?spec=4">Find Pediatricians</a>',
            'eye': '👁️ Eye problems are treated by an <strong>Ophthalmologist</strong>. <a href="doctors.php?spec=7">Find Eye Doctors</a>',
            'pregnancy': '🤱 For pregnancy care, consult a <strong>Gynecologist</strong>. <a href="doctors.php?spec=6">Find Gynecologists</a>',
        };
        for (const [key, val] of Object.entries(map)) {
            if (s.includes(key)) return val;
        }
        return `I understand you mentioned: "<em>${symptom}</em>". For a proper diagnosis, please <a href="doctors.php">find a doctor</a> or describe more specific symptoms.`;
    }

    // ---- Prescription medicine add rows ----
    const addMedBtn = document.getElementById('add-medicine');
    if (addMedBtn) {
        addMedBtn.addEventListener('click', () => {
            const container = document.getElementById('medicines-container');
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 medicine-row-item';
            row.innerHTML = `
                <div class="col-md-4"><input type="text" name="med_name[]" class="form-control" placeholder="Medicine name" required></div>
                <div class="col-md-3"><input type="text" name="med_dose[]" class="form-control" placeholder="Dosage (e.g. 500mg)"></div>
                <div class="col-md-3"><input type="text" name="med_freq[]" class="form-control" placeholder="Frequency (e.g. 3x/day)"></div>
                <div class="col-md-2"><button type="button" class="btn btn-sm btn-danger remove-med w-100"><i class="fas fa-trash"></i></button></div>
            `;
            container.appendChild(row);
            row.querySelector('.remove-med').addEventListener('click', () => row.remove());
        });
    }

    // ---- Filter form submit on select change ----
    document.querySelectorAll('.auto-submit').forEach(el => {
        el.addEventListener('change', () => el.closest('form').submit());
    });
});
