document.addEventListener('DOMContentLoaded', function() {
        // Theme toggle
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        
        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
        updateThemeButton(savedTheme);
        
        themeToggle.addEventListener('click', function() {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeButton(newTheme);
        });
        
        function updateThemeButton(theme) {
            const icon = theme === 'dark' ? 'fa-sun' : 'fa-moon';
            const text = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
            themeToggle.innerHTML = `<i class="fas ${icon}"></i> ${text}`;
        }
        
        // Cipher type change
        const cipherSelect = document.getElementById('cipher');
        const keyLabel = document.getElementById('keyLabel');
        
        cipherSelect.addEventListener('change', function() {
            keyLabel.textContent = this.value === 'caesar' ? 'Shift Number:' : 'Keyword:';
        });
        
        // Clear button
        const clearBtn = document.getElementById('clearBtn');
        clearBtn.addEventListener('click', function() {
            document.getElementById('message').value = '';
            document.getElementById('result').value = '';
            document.getElementById('key').value = '';
            document.querySelector('input[name="operation"][value="encrypt"]').checked = true;
            document.getElementById('message').focus();
        });
        
        // Copy result button
        const copyBtn = document.getElementById('copyBtn');
        copyBtn.addEventListener('click', function() {
            const resultTextarea = document.getElementById('result');
            if (resultTextarea.value.trim()) {
                resultTextarea.select();
                document.execCommand('copy');
                
                // Visual feedback
                const originalText = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => {
                    copyBtn.innerHTML = originalText;
                }, 2000);
            }
        });
        
        // Form validation
        const cipherForm = document.getElementById('cipherForm');
        cipherForm.addEventListener('submit', function(e) {
            const cipher = cipherSelect.value;
            const key = document.getElementById('key').value;
            
            if (cipher === 'caesar' && !/^\d+$/.test(key)) {
                alert('Shift must be a number for Caesar cipher');
                e.preventDefault();
                return;
            }
            
            if ((cipher === 'vigenere' || cipher === 'playfair') && !key.trim()) {
                alert('Please enter a keyword');
                e.preventDefault();
                return;
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+N for new/clear
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                clearBtn.click();
            }
            
            // Ctrl+C for copy (only when not in input fields)
            if (e.ctrlKey && e.key === 'c' && !['TEXTAREA', 'INPUT'].includes(document.activeElement.tagName)) {
                e.preventDefault();
                copyBtn.click();
            }
        });
    });