let currentPhotos = [];
let currentIndex = 0;
let iso;

document.addEventListener("DOMContentLoaded", function () {
  const grid = document.querySelector('#photo-grid');
  if (grid && grid.children.length > 0) {
    iso = new Isotope(grid, { // [2]
      itemSelector: '.group-thumb', // [2]
      layoutMode: 'fitRows' // [2]
    });

    const filterButtons = document.querySelectorAll('.filters ul li'); // [3]
    filterButtons.forEach(button => {
      button.addEventListener('click', () => {
        filterButtons.forEach(btn => btn.classList.remove('active')); // [3]
        button.classList.add('active'); // [3]
        const filterValue = button.getAttribute('data-filter'); // [3]
        iso.arrange({ filter: filterValue }); // [3]
      });
    });
  }
});


function showPopup(photos) {
  currentPhotos = photos;
  currentIndex = 0;
  document.getElementById('popup-container').style.display = 'flex';
  updatePopupImage();
  updateThumbnailList();
}

function closePopup() {
  document.getElementById('popup-container').style.display = 'none';
}

function updatePopupImage() {
  const photoUrl = `/alwafahub/uploads/${CLIENT_SLUG}/${currentPhotos[currentIndex]}`;
  document.getElementById('popup-image').src = photoUrl;
  document.getElementById('download-link').href = photoUrl;
  document.getElementById('popup-number').innerText = `${currentIndex + 1} / ${currentPhotos.length}`;
}

function nextImage() {
  currentIndex = (currentIndex + 1) % currentPhotos.length;
  updatePopupImage();
}

function prevImage() {
  currentIndex = (currentIndex - 1 + currentPhotos.length) % currentPhotos.length;
  updatePopupImage();
}

function updateThumbnailList() {
  const thumbList = document.getElementById('thumbnail-list');
  thumbList.innerHTML = '';
  currentPhotos.forEach((photo, index) => {
    const thumbUrl = `/alwafahub/uploads/${CLIENT_SLUG}/thumbs/${photo}`;
    const img = document.createElement('img');
    img.src = thumbUrl;
    img.dataset.photo = photo;
    img.classList.add('collage-thumb');
    img.onclick = () => toggleThumbnailSelection(img);
    thumbList.appendChild(img);
  });
}

function toggleThumbnailSelection(imgElement) {
    imgElement.classList.toggle('selected');
    const selected = document.querySelectorAll('#thumbnail-list .selected');
    document.getElementById('make-collage-btn').style.display = selected.length === 2 ? 'inline-block' : 'none';
}

function makeCollage() {
    const selected = document.querySelectorAll('#thumbnail-list .selected');
    if (selected.length !== 2) {
        alert('Silakan pilih tepat 2 foto.');
        return;
    }

    const photo1 = selected[0].dataset.photo;
    const photo2 = selected[1].dataset.photo;
    
    // Kirim ke server untuk dibuatkan kolase
    const collageUrl = `/alwafahub/ajax/make_collage.php?client=${CLIENT_SLUG}&photo1=${photo1}&photo2=${photo2}`;
    
    // Untuk demo, kita langsung buka di tab baru
    window.open(collageUrl, '_blank');
}

// ZIP Download
document.getElementById('zip-link')?.addEventListener('click', function(e) {
    e.preventDefault();
    alert('Fungsi download ZIP sedang dalam pengembangan. Untuk saat ini, silakan unduh foto satu per satu.');
    // Logic untuk ZIP bisa ditambahkan di sini menggunakan library seperti JSZip
});
