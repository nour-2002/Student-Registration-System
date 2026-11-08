'use strict';

document.querySelectorAll('.password-toggle').forEach(button => {
  button.addEventListener('click', () => {
    const input = document.getElementById(button.getAttribute('aria-controls'));
    const shown = input.type === 'password';
    input.type = shown ? 'text' : 'password';
    button.textContent = shown ? 'إخفاء' : 'إظهار';
    button.setAttribute('aria-pressed', String(shown));
  });
});

async function prepareStaticPages() {
  const form = document.querySelector('[data-static-auth]');
  const homeLogin = document.querySelector('[data-login-link]');
  if (!form && !homeLogin) return;
  const submit = form?.querySelector('[type=submit]');
  if (submit) submit.disabled = true;
  try {
    const response = await fetch('session_info.php', { headers: { Accept: 'application/json' }, cache: 'no-store' });
    if (!response.ok) throw new Error('session');
    const data = await response.json();
    if (form) {
      if (data.username) { location.replace('dashboard.php'); return; }
      form.elements.csrf.value = data.csrf;
      submit.disabled = false;
    } else if (data.username) {
      homeLogin.textContent = 'مساحة العمل'; homeLogin.href = 'dashboard.php';
      const register = document.querySelector('[data-register-link]');
      register.textContent = 'تسجيل طالب'; register.href = 'add_student.php';
    }
  } catch {
    if (form) {
      const notice = document.createElement('p'); notice.className = 'field-error'; notice.setAttribute('role', 'alert');
      notice.textContent = 'تعذر تجهيز النموذج. حدّث الصفحة أو استخدم النموذج المباشر.';
      const fallback = document.createElement('a'); fallback.href = form.getAttribute('action'); fallback.textContent = 'فتح النموذج المباشر';
      form.before(notice, fallback);
    }
  }
}
prepareStaticPages();

const studentForm = document.getElementById('student-form');
if (studentForm) {
  const fields = ['name', 'email', 'phone', 'address'];
  const touched = new Set();
  let timer, controller, requestNumber = 0, photoUrl;
  const status = document.getElementById('validation-status');
  const displayError = (field, message) => {
    const input = studentForm.elements[field];
    document.getElementById('error-' + field).textContent = message || '';
    input.classList.toggle('is-invalid', Boolean(message));
    input.setAttribute('aria-invalid', String(Boolean(message)));
  };
  async function validateAll() {
    controller?.abort(); controller = new AbortController();
    const number = ++requestNumber;
    const body = new FormData();
    fields.forEach(field => body.set(field, studentForm.elements[field].value));
    body.set('csrf', studentForm.elements.csrf.value);
    status.textContent = 'جارٍ التحقق من البيانات…';
    try {
      const response = await fetch(studentForm.dataset.validationUrl, { method: 'POST', body, signal: controller.signal, headers: { Accept: 'application/json' } });
      const result = await response.json();
      if (number !== requestNumber) return;
      if (!response.ok) throw new Error(result.message || 'تعذر التحقق الآن. يمكنك الحفظ ليتحقق الخادم من البيانات.');
      touched.forEach(field => displayError(field, result.errors[field]));
      status.textContent = result.valid ? 'الحقول النصية جاهزة للحفظ.' : 'تحقق من الحقول قبل الحفظ.';
    } catch (error) {
      if (error.name !== 'AbortError' && number === requestNumber) status.textContent = error.message;
    }
  }
  fields.forEach(field => {
    const input = studentForm.elements[field];
    input.addEventListener('input', () => {
      touched.add(field);
      document.getElementById('preview-' + field).textContent = input.value.trim() || (field === 'name' ? 'اسم الطالب' : '—');
      clearTimeout(timer); timer = setTimeout(validateAll, 350);
      if (field === 'name') {
        const initials = document.querySelector('#preview-avatar .initials');
        if (initials) initials.textContent = Array.from(input.value.trim())[0] || 'م';
      }
    });
    input.addEventListener('blur', () => { touched.add(field); clearTimeout(timer); validateAll(); });
  });
  studentForm.elements.picture.addEventListener('change', event => {
    const file = event.target.files[0];
    const message = document.getElementById('error-picture');
    message.textContent = '';
    if (!file) return;
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2097152) {
      message.textContent = 'اختر JPG أو PNG أو WebP بحجم لا يتجاوز 2 ميغابايت.';
      event.target.value = ''; return;
    }
    if (photoUrl) URL.revokeObjectURL(photoUrl);
    photoUrl = URL.createObjectURL(file);
    const image = document.createElement('img'); image.src = photoUrl; image.alt = 'معاينة الصورة المختارة'; image.className = 'avatar avatar-large'; image.width = image.height = 90;
    document.getElementById('preview-avatar').replaceChildren(image);
    const remove = studentForm.elements.remove_photo;
    if (remove) remove.checked = false;
  });
  studentForm.addEventListener('submit', () => {
    clearTimeout(timer); controller?.abort();
    // The server always validates again; submitting does not depend on AJAX availability.
    status.textContent = 'جارٍ حفظ السجل…';
    const button = studentForm.querySelector('[type=submit]');
    button.disabled = true;
  });
}
