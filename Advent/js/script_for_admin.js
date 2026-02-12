// ======= вкладки =======
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click',()=>{ 
        document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c=>c.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById('tab-'+tab.dataset.tab).classList.add('active');
    });
});

// ======= синхронизация контента =======
function syncContent(){
    document.getElementById('content').value = document.getElementById('editor').innerHTML;
    document.getElementById('footer').value = document.getElementById('editor-footer').innerHTML;
}

// ======= удаление картинки =======
function deleteImage(){
    const form = document.getElementById('dayForm');
    syncContent();

    let inp = form.querySelector('input[name="delete_image"]');
    if(!inp){
        inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'delete_image';
        form.appendChild(inp);
    }
    inp.value = 1;

    const formData = new FormData(form);
    fetch('', {
        method:'POST',
        body: formData,
        headers: { 'X-Requested-With':'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if(data.saved){
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            inp.remove();
        }
    });
}

// ======= AJAX сохранение дня =======
document.getElementById('dayForm').addEventListener('submit', function(e){
    e.preventDefault();
    syncContent();
    const formData = new FormData(this);
    fetch('', { method:'POST', body:formData, headers:{'X-Requested-With':'XMLHttpRequest'} })
      .then(res=>res.json())
      .then(data=>{ if(data.saved) showNotice(); });
});

// ======= AJAX сохранение глобальных стилей =======
document.getElementById('stylesForm').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    fetch('', { method:'POST', body:formData, headers:{'X-Requested-With':'XMLHttpRequest'} })
      .then(res=>res.json())
      .then(data=>{ if(data.saved) showNotice(); });
});


// ======= превью =======
function openPreview(){
    window.open('../php/admin_advent_index.php','AdventPreview','width=1000,height=800,scrollbars=yes');
}

// ======= выбор дня =======
document.getElementById('daySelect').addEventListener('change', function(){
    const day = this.value;
    window.location.href='?day='+day;
});