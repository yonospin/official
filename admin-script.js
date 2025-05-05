// Preview uploaded logo
document.addEventListener('DOMContentLoaded', function() {
    // Logo preview for Add New App form
    const appLogoInput = document.getElementById('app_logo');
    const logoPreview = document.getElementById('logo-preview');
    
    if (appLogoInput) {
        appLogoInput.addEventListener('change', function() {
            previewImage(this, logoPreview);
        });
    }
    
    // Logo preview for Modify App form
    const modifyAppLogoInput = document.getElementById('modify_app_logo');
    const modifyLogoPreview = document.getElementById('modify-logo-preview');
    
    if (modifyAppLogoInput) {
        modifyAppLogoInput.addEventListener('change', function() {
            previewImage(this, modifyLogoPreview);
        });
    }
    
    // Modal functionality
    const modal = document.getElementById('modifyModal');
    const closeBtn = document.getElementsByClassName('close')[0];
    
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Auto-hide success message after 5 seconds
    const successMessage = document.querySelector('.success-message');
    if (successMessage) {
        setTimeout(function() {
            successMessage.style.display = 'none';
        }, 5000);
    }
});

// Function to preview uploaded image
function previewImage(input, previewElement) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewElement.src = e.target.result;
            previewElement.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Function to open modify modal
function openModifyModal(appId, appName, bonus, withdraw, imageSrc, landingPage, downloadLink) {
    const modal = document.getElementById('modifyModal');
    const appIdInput = document.getElementById('modify_app_id');
    const appNameInput = document.getElementById('modify_app_name');
    const bonusInput = document.getElementById('modify_bonus_amount');
    const withdrawInput = document.getElementById('modify_min_withdraw');
    const downloadLinkInput = document.getElementById('modify_download_link');
    const landingPageInfo = document.getElementById('landing_page_info');
    const currentLogo = document.getElementById('current_logo');
    
    // Set values
    appIdInput.value = appId;
    appNameInput.value = appName;
    bonusInput.value = bonus;
    withdrawInput.value = withdraw;
    downloadLinkInput.value = downloadLink;
    currentLogo.src = imageSrc;
    
    // Set landing page info
    if (landingPage && landingPage !== '#') {
        landingPageInfo.innerHTML = `
            <p>Current landing page: <a href="${landingPage}" target="_blank">${landingPage}</a></p>
            <p class="info-text">Note: A new landing page will be generated with your changes.</p>
        `;
    } else {
        landingPageInfo.innerHTML = `
            <p>No landing page exists yet. One will be created when you save.</p>
        `;
    }
    
    // Show modal
    modal.style.display = 'block';
}