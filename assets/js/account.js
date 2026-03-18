document.addEventListener('DOMContentLoaded', () => {
    document.body.classList.add('account-page-active');

    initAccountTabs();
    initAccountDropdowns();
    initAvatarCropper();
});

function initAccountTabs() {
    const dashboard = document.querySelector('[data-account-dashboard]');

    if (!dashboard) {
        return;
    }

    const buttons = dashboard.querySelectorAll('[data-tab-target]');
    const panes = dashboard.querySelectorAll('[data-tab-pane]');
    const activeTab = dashboard.dataset.activeTab;

    const activateTab = (tabName) => {
        buttons.forEach((button) => {
            button.classList.toggle('active', button.dataset.tabTarget === tabName);
        });

        panes.forEach((pane) => {
            pane.classList.toggle('active', pane.dataset.tabPane === tabName);
        });
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            activateTab(button.dataset.tabTarget);
        });
    });

    if (activeTab) {
        activateTab(activeTab);
    }
}

function initAccountDropdowns() {
    document.addEventListener('click', (event) => {
        const toggleButton = event.target.closest('[data-dropdown-toggle]');

        if (toggleButton) {
            const currentDropdown = toggleButton.closest('.account-dropdown');

            document.querySelectorAll('.account-dropdown.open').forEach((dropdown) => {
                if (dropdown !== currentDropdown) {
                    dropdown.classList.remove('open');
                }
            });

            currentDropdown.classList.toggle('open');
            return;
        }

        if (!event.target.closest('.account-dropdown')) {
            document.querySelectorAll('.account-dropdown.open').forEach((dropdown) => {
                dropdown.classList.remove('open');
            });
        }
    });
}

function initAvatarCropper() {
    const avatarInput = document.getElementById('avatarInput');
    const cropperImage = document.getElementById('cropperImage');
    const cropperContainer = document.getElementById('cropperContainer');
    const saveAvatarBtn = document.getElementById('saveAvatarBtn');
    const avatarPreview = document.getElementById('avatarPreview');
    const sidebarAvatarPreview = document.getElementById('sidebarAvatarPreview');
    const avatarMessage = document.getElementById('avatarMessage');
    const avatarToken = document.getElementById('avatarToken');
    const avatarUploadUrlInput = document.getElementById('avatarUploadUrl');

    if (
        !avatarInput ||
        !cropperImage ||
        !cropperContainer ||
        !saveAvatarBtn ||
        !avatarPreview ||
        !avatarMessage ||
        !avatarToken ||
        !avatarUploadUrlInput
    ) {
        return;
    }

    let cropper = null;

    avatarInput.addEventListener('change', (event) => {
        const file = event.target.files[0];

        if (!file) {
            return;
        }

        if (!file.type.startsWith('image/')) {
            avatarMessage.innerHTML = '<div class="text-danger">Выберите изображение</div>';
            return;
        }

        const reader = new FileReader();

        reader.onload = (loadEvent) => {
            cropperImage.src = loadEvent.target.result;
            cropperContainer.style.display = 'block';
            avatarMessage.innerHTML = '';

            if (cropper) {
                cropper.destroy();
            }

            cropper = new Cropper(cropperImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                responsive: true,
                background: false
            });
        };

        reader.readAsDataURL(file);
    });

    saveAvatarBtn.addEventListener('click', () => {
        if (!cropper) {
            avatarMessage.innerHTML = '<div class="text-danger">Сначала выберите изображение</div>';
            return;
        }

        const canvas = cropper.getCroppedCanvas({
            width: 300,
            height: 300,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        const avatarData = canvas.toDataURL('image/webp', 0.9);
        const formData = new FormData();

        formData.append('avatar', avatarData);
        formData.append('_token', avatarToken.value);

        fetch(avatarUploadUrlInput.value, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    avatarMessage.innerHTML = `<div class="text-danger">${data.message}</div>`;
                    return;
                }

                const avatarUrl = `${data.avatarUrl}?v=${new Date().getTime()}`;

                avatarPreview.src = avatarUrl;

                if (sidebarAvatarPreview) {
                    sidebarAvatarPreview.src = avatarUrl;
                }

                avatarMessage.innerHTML = `<div class="text-success">${data.message}</div>`;
            })
            .catch(() => {
                avatarMessage.innerHTML = '<div class="text-danger">Ошибка при загрузке аватара</div>';
            });
    });
}
