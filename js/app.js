/* ============================================
   Back2U Lost & Found — Core JavaScript
   Handles UI interactions, utilities, and shared
   functionality across all pages.
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {

    // ========================================
    // 1. DARK MODE
    // ========================================
    const darkModeKey = 'darkMode';
    const darkModeToggle = document.querySelector('.dark-mode-toggle');
    const darkModeCheckbox = document.getElementById('darkModeSwitch');

    function applyDarkMode(enabled) {
        if (enabled) {
            document.body.classList.add('dark-mode');
            if (darkModeToggle) darkModeToggle.classList.add('active');
            if (darkModeCheckbox) darkModeCheckbox.checked = true;
        } else {
            document.body.classList.remove('dark-mode');
            if (darkModeToggle) darkModeToggle.classList.remove('active');
            if (darkModeCheckbox) darkModeCheckbox.checked = false;
        }
    }

    // Initialise from localStorage
    const savedDarkMode = localStorage.getItem(darkModeKey);
    if (savedDarkMode === 'true') {
        applyDarkMode(true);
    }

    // Global toggle function
    window.toggleDarkMode = function () {
        const isActive = !document.body.classList.contains('dark-mode');
        applyDarkMode(isActive);
        localStorage.setItem(darkModeKey, isActive);
    };

    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', window.toggleDarkMode);
    }

    if (darkModeCheckbox) {
        darkModeCheckbox.addEventListener('change', function () {
            const isActive = darkModeCheckbox.checked;
            applyDarkMode(isActive);
            localStorage.setItem(darkModeKey, isActive);
        });
    }

    // ========================================
    // 2. MOBILE NAV TOGGLE
    // ========================================
    const mobileToggleBtn = document.querySelector('.mobile-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (mobileToggleBtn && navLinks) {
        mobileToggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            navLinks.classList.toggle('show');
        });

        // Close nav when clicking outside
        document.addEventListener('click', function (e) {
            if (navLinks.classList.contains('show') &&
                !navLinks.contains(e.target) &&
                !mobileToggleBtn.contains(e.target)) {
                navLinks.classList.remove('show');
            }
        });

        // Close nav when a link is clicked (mobile)
        navLinks.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                navLinks.classList.remove('show');
            });
        });
    }

    // ========================================
    // 3. TOAST NOTIFICATIONS
    // ========================================
    window.showToast = function (message, type, duration) {
        type = type || 'success';
        duration = duration || 3000;

        // Ensure container exists
        var container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        // Icon mapping
        var icons = {
            success: 'fa-circle-check',
            error: 'fa-circle-exclamation',
            info: 'fa-circle-info'
        };

        var toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.innerHTML =
            '<i class="fa-solid ' + (icons[type] || icons.info) + '"></i>' +
            '<span>' + message + '</span>' +
            '<button class="toast-close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>';

        container.appendChild(toast);

        // Close on click
        var closeBtn = toast.querySelector('.toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                removeToast(toast);
            });
        }

        // Auto-remove
        var timer = setTimeout(function () {
            removeToast(toast);
        }, duration);

        function removeToast(t) {
            clearTimeout(timer);
            t.style.opacity = '0';
            t.style.transform = 'translateX(100%)';
            t.style.transition = 'all 0.3s ease';
            setTimeout(function () {
                if (t.parentNode) t.parentNode.removeChild(t);
            }, 300);
        }
    };

    // ========================================
    // 4. CONFIRMATION MODAL
    // ========================================
    window.showConfirm = function (title, message, type, confirmText, cancelText) {
        type = type || 'warning';
        confirmText = confirmText || 'Confirm';
        cancelText = cancelText || 'Cancel';

        return new Promise(function (resolve) {
            // Icon mapping
            var iconMap = {
                warning: 'fa-triangle-exclamation',
                danger: 'fa-trash-can',
                success: 'fa-circle-check'
            };

            // Button class mapping
            var btnMap = {
                warning: 'btn-warning',
                danger: 'btn-danger',
                success: 'btn-success'
            };

            var overlay = document.createElement('div');
            overlay.className = 'modal-overlay';
            overlay.innerHTML =
                '<div class="modal-content">' +
                '  <div class="modal-icon ' + type + '">' +
                '    <i class="fa-solid ' + (iconMap[type] || iconMap.warning) + '"></i>' +
                '  </div>' +
                '  <h3 class="modal-title">' + title + '</h3>' +
                '  <p class="modal-body">' + message + '</p>' +
                '  <div class="modal-actions">' +
                '    <button class="btn btn-ghost" id="modal-cancel">' + cancelText + '</button>' +
                '    <button class="btn ' + (btnMap[type] || 'btn-warning') + '" id="modal-confirm">' + confirmText + '</button>' +
                '  </div>' +
                '</div>';

            document.body.appendChild(overlay);

            // Trigger animation
            requestAnimationFrame(function () {
                overlay.classList.add('active');
            });

            function close(result) {
                overlay.classList.remove('active');
                setTimeout(function () {
                    if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
                }, 300);
                resolve(result);
            }

            overlay.querySelector('#modal-confirm').addEventListener('click', function () {
                close(true);
            });

            overlay.querySelector('#modal-cancel').addEventListener('click', function () {
                close(false);
            });

            // Close on backdrop click
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    close(false);
                }
            });

            // Close on Escape key
            function handleEsc(e) {
                if (e.key === 'Escape') {
                    document.removeEventListener('keydown', handleEsc);
                    close(false);
                }
            }
            document.addEventListener('keydown', handleEsc);
        });
    };

    // ========================================
    // 5. IMAGE UPLOAD PREVIEW
    // ========================================
    window.initUploadZone = function (inputId, previewId, zoneId) {
        var input = document.getElementById(inputId);
        var preview = document.getElementById(previewId);
        var zone = document.getElementById(zoneId);

        if (!input || !zone) return;

        var maxSize = 5 * 1024 * 1024; // 5MB
        var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        function handleFile(file) {
            if (!file) return;

            // Validate type
            if (allowedTypes.indexOf(file.type) === -1) {
                window.showToast('Please upload an image file (JPEG, PNG, GIF, or WebP).', 'error');
                return;
            }

            // Validate size
            if (file.size > maxSize) {
                window.showToast('File is too large. Maximum size is 5MB.', 'error');
                return;
            }

            // Show preview
            if (preview) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var sizeText = (file.size / 1024).toFixed(1) + ' KB';
                    if (file.size > 1024 * 1024) {
                        sizeText = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                    }
                    preview.innerHTML =
                        '<img src="' + e.target.result + '" alt="Preview">' +
                        '<div class="file-info"><strong>' + file.name + '</strong> — ' + sizeText + '</div>';
                };
                reader.readAsDataURL(file);
            }
        }

        // Click to select file
        zone.addEventListener('click', function () {
            input.click();
        });

        // File selected via input
        input.addEventListener('change', function () {
            if (input.files && input.files[0]) {
                handleFile(input.files[0]);
            }
        });

        // Drag & Drop
        zone.addEventListener('dragover', function (e) {
            e.preventDefault();
            zone.classList.add('dragover');
        });

        zone.addEventListener('dragleave', function (e) {
            e.preventDefault();
            zone.classList.remove('dragover');
        });

        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            zone.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                // Update the file input so forms still work
                input.files = e.dataTransfer.files;
                handleFile(e.dataTransfer.files[0]);
            }
        });
    };

    // ========================================
    // 6. NOTIFICATION BADGE UPDATES
    // ========================================
    window.updateNotifications = function () {
        var badge = document.querySelector('.notification-badge');
        if (!badge) return;

        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'php/api_notifications.php', true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    var count = parseInt(data.total, 10) || 0;
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                } catch (e) {
                    // Silently fail – notification endpoint may not exist yet
                }
            }
        };
        xhr.send();
    };

    // Initial fetch & polling every 30 seconds
    window.updateNotifications();
    setInterval(window.updateNotifications, 30000);

    // ========================================
    // 7. ANIMATED COUNTER
    // ========================================
    window.animateCounter = function (element, target, duration) {
        duration = duration || 1000;
        target = parseInt(target, 10);
        if (isNaN(target)) return;

        var start = 0;
        var startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            // Ease-out curve
            var easedProgress = 1 - Math.pow(1 - progress, 3);
            var current = Math.floor(easedProgress * target);
            element.textContent = current.toLocaleString();
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                element.textContent = target.toLocaleString();
            }
        }

        requestAnimationFrame(step);
    };

    // Auto-animate stat values on page load
    document.querySelectorAll('.stat-value[data-target]').forEach(function (el) {
        var target = el.getAttribute('data-target');
        window.animateCounter(el, target, 1200);
    });

    // ========================================
    // 8. PAGE LOADER
    // ========================================
    var pageLoader = document.querySelector('.page-loader');
    if (pageLoader) {
        // Give a small delay so content has time to render
        setTimeout(function () {
            pageLoader.classList.add('hidden');
            // Remove from DOM after transition
            setTimeout(function () {
                if (pageLoader.parentNode) {
                    pageLoader.parentNode.removeChild(pageLoader);
                }
            }, 300);
        }, 300);
    }

    // ========================================
    // 9. URL PARAMS HELPER & FLASH MESSAGES
    // ========================================
    window.getUrlParam = function (name) {
        var params = new URLSearchParams(window.location.search);
        return params.get(name);
    };

    // Flash message mapping
    var flashMessages = {
        uploaded: { text: 'Item uploaded successfully!', type: 'success' },
        claimed: { text: 'Claim submitted successfully!', type: 'success' },
        resolved: { text: 'Item marked as resolved!', type: 'success' },
        deleted: { text: 'Item deleted successfully.', type: 'success' },
        updated: { text: 'Item updated successfully!', type: 'success' },
        login: { text: 'Welcome back!', type: 'success' },
        register: { text: 'Account created! Welcome to Back2U.', type: 'success' },
        logout: { text: 'You have been logged out.', type: 'info' },
        error: { text: 'Something went wrong. Please try again.', type: 'error' },
        denied: { text: 'Access denied. Please log in.', type: 'error' },
        invalid: { text: 'Invalid request. Please try again.', type: 'error' }
    };

    var msgParam = window.getUrlParam('msg');
    if (msgParam && flashMessages[msgParam]) {
        var flash = flashMessages[msgParam];
        // Slight delay so the toast appears after page is rendered
        setTimeout(function () {
            window.showToast(flash.text, flash.type);
        }, 500);
        // Clean URL (remove msg param)
        if (window.history && window.history.replaceState) {
            var cleanUrl = window.location.pathname + window.location.hash;
            window.history.replaceState(null, '', cleanUrl);
        }
    }

    // ========================================
    // 10. SMOOTH SCROLL
    // ========================================
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ========================================
    // 11. CLICK OUTSIDE — CLOSE MODALS & DROPDOWNS
    // ========================================
    document.addEventListener('click', function (e) {
        // Close any active modals on backdrop click
        document.querySelectorAll('.modal-overlay.active').forEach(function (modal) {
            if (e.target === modal) {
                modal.classList.remove('active');
                setTimeout(function () {
                    if (modal.parentNode) modal.parentNode.removeChild(modal);
                }, 300);
            }
        });

        // Close dropdown menus
        document.querySelectorAll('.dropdown-menu.show').forEach(function (menu) {
            if (!menu.contains(e.target) && !menu.previousElementSibling.contains(e.target)) {
                menu.classList.remove('show');
            }
        });
    });

    // Escape key to close active modals
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(function (modal) {
                modal.classList.remove('active');
                setTimeout(function () {
                    if (modal.parentNode) modal.parentNode.removeChild(modal);
                }, 300);
            });
        }
    });

}); // end DOMContentLoaded
