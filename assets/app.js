// =========================
// Core
// =========================
import './js/jquery-global.js';

import 'swiper/css/bundle';
import 'perfect-scrollbar/css/perfect-scrollbar.css';

import { createIcons, icons } from 'lucide';

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});

// =========================
// CSS
// =========================
import './vendor/bootstrap.min.css';
import './vendor/animate.min.css';
import './vendor/slicknav.css';
import './vendor/athena-icons.css';
import './vendor/ui-icon.css';

import './styles/style.css';
import './styles/components.css';
import './styles/widgets.css';
import './styles/responsive.css';
import './styles/post-create.css';
import './styles/account.css';

// =========================
// Bootstrap JS
// =========================
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// =========================
// Vendor JS
// =========================
import './js/vendor/jquery.slicknav.js';
import './js/vendor/jquery.scrollUp.min.js';

// =========================
// Cropper
// =========================
import 'cropperjs/dist/cropper.css';
import Cropper from 'cropperjs';

window.Cropper = Cropper;
globalThis.Cropper = Cropper;

// =========================
// Main JS
// =========================
import './js/main.js';
import './js/account.js';

