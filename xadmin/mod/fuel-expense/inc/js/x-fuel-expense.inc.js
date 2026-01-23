/**
 * Fuel Expense Module - OCR Bill Image Handler
 * Handles image upload and automatic data extraction via OCR with loader
 */

(function() {
    'use strict';

    // Helper to show messages - uses mxMsg if available, otherwise uses browser alert
    function showMessage(msg, type) {
        console.log('[OCR] Message (' + type + '):', msg);
        if (typeof mxMsg === 'function') {
            mxMsg(msg, type);
        } else {
            console.warn('[OCR] mxMsg not available, using alert fallback');
            if (type === 'success') {
                alert('✓ ' + msg);
            } else if (type === 'error') {
                alert('✗ ' + msg);
            } else {
                alert(msg);
            }
        }
    }

    // Create and show loader
    function showLoader() {
        console.log('[OCR] Showing loader...');
        var loaderHTML = '<div id="ocrLoader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 9999;"><div style="background: white; padding: 30px; border-radius: 8px; text-align: center; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);"><div style="border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div><h3 style="margin: 0 0 10px 0; color: #333;">Processing Bill Image</h3><p style="margin: 0; color: #666; font-size: 14px;">Extracting date and amount via OCR...</p></div><style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style></div>';

        var existingLoader = document.getElementById('ocrLoader');
        if (existingLoader) {
            existingLoader.remove();
        }

        document.body.insertAdjacentHTML('beforeend', loaderHTML);
    }

    // Hide loader
    function hideLoader() {
        console.log('[OCR] Hiding loader...');
        var loader = document.getElementById('ocrLoader');
        if (loader) {
            loader.remove();
        }
    }

    function handleBillImageChange(event) {
        console.log('[OCR] handleBillImageChange triggered');
        var fileInput = this;
        var file = fileInput.files[0];

        if (!file) {
            console.log('[OCR] No file selected');
            return;
        }

        console.log('[OCR] File selected:', file.name, 'Size:', file.size, 'Type:', file.type);

        // Validate file type
        var allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        var fileName = file.name.toLowerCase();
        var isValidType = allowedTypes.includes(file.type) || fileName.endsWith('.jpg') || fileName.endsWith('.jpeg') || fileName.endsWith('.png') || fileName.endsWith('.pdf');

        if (!isValidType) {
            var msg = 'Invalid file type. Please upload JPG, PNG, or PDF files only.';
            console.log('[OCR] ' + msg);
            showMessage(msg, 'error');
            fileInput.value = '';
            return;
        }

        // Validate file size (5MB max)
        if (file.size > 5242880) {
            var msg = 'File size exceeds 5MB limit.';
            console.log('[OCR] ' + msg);
            showMessage(msg, 'error');
            fileInput.value = '';
            return;
        }

        // Show loader
        showLoader();

        // Prepare FormData for upload
        var formData = new FormData();
        formData.append('xAction', 'OCR');
        formData.append('billImage', file);

        console.log('[OCR] Sending OCR request for file:', file.name);

        // Send file to server for OCR processing
        fetch('/xadmin/mod/fuel-expense/x-fuel-expense.inc.php', {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            console.log('[OCR] Response Status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP error ' + response.status);
            }
            return response.text();
        })
        .then(function(text) {
            console.log('[OCR] Response text length:', text.length);
            console.log('[OCR] First 100 chars:', text.substring(0, 100));

            // Try to parse JSON
            var data;
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                console.error('[OCR] JSON Parse Error:', parseError);
                console.error('[OCR] Full response text:', text);
                hideLoader();

                var errorMsg = 'Invalid response from server (not valid JSON).\n\nResponse preview:\n' + text.substring(0, 150);
                showMessage(errorMsg, 'error');
                return;
            }

            console.log('[OCR] Response received:', data);
            hideLoader();

            // Debug: Log all response details
            console.log('[OCR] data.err =', data.err);
            console.log('[OCR] data.data =', data.data);
            console.log('[OCR] data.msg =', data.msg);

            if (data.err === 0 && data.data) {
                // OCR succeeded
                var ocrData = data.data;
                console.log('[OCR] Processing extracted data...');
                console.log('[OCR] ocrData =', ocrData);
                console.log('[OCR] ocrData.date =', ocrData.date);
                console.log('[OCR] ocrData.amount =', ocrData.amount);
                console.log('[OCR] ocrData.filename =', ocrData.filename);

                // Store filename for form submission - create a hidden field if it doesn't exist
                var billImageFilenameInput = document.querySelector('input[name="billImage_ocr_filename"]');
                if (!billImageFilenameInput) {
                    billImageFilenameInput = document.createElement('input');
                    billImageFilenameInput.type = 'hidden';
                    billImageFilenameInput.name = 'billImage_ocr_filename';
                    var form = document.querySelector('form[name="frmAddEdit"]') || document.querySelector('form#frmAddEdit');
                    if (form) {
                        form.appendChild(billImageFilenameInput);
                    }
                }
                if (billImageFilenameInput && ocrData.filename) {
                    billImageFilenameInput.value = ocrData.filename;
                    console.log('[OCR] OCR filename stored in hidden field for form submission');
                }

                // Find and populate bill date
                var dateInput = document.querySelector('input[name="billDate"]');
                console.log('[OCR] dateInput element found:', dateInput ? 'YES' : 'NO');

                if (ocrData.date && dateInput) {
                    var parts = ocrData.date.split('-');
                    console.log('[OCR] Date parts:', parts);
                    if (parts.length === 3) {
                        var formattedDate = parts[1] + '/' + parts[2] + '/' + parts[0];
                        dateInput.value = formattedDate;
                        dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                        console.log('[OCR] Date field updated:', formattedDate);
                    } else {
                        console.log('[OCR] Date parts length not 3, got:', parts.length);
                    }
                } else {
                    console.log('[OCR] Date not populated: date=' + (ocrData.date ? 'exists' : 'missing') + ', dateInput=' + (dateInput ? 'found' : 'not found'));
                }

                // Find and populate expense amount
                var amountInput = document.querySelector('input[name="expenseAmount"]');
                console.log('[OCR] amountInput element found:', amountInput ? 'YES' : 'NO');

                if (ocrData.amount && amountInput) {
                    amountInput.value = ocrData.amount;
                    amountInput.dispatchEvent(new Event('change', { bubbles: true }));
                    console.log('[OCR] Amount field updated:', ocrData.amount);
                } else {
                    console.log('[OCR] Amount not populated: amount=' + (ocrData.amount ? 'exists' : 'missing') + ', amountInput=' + (amountInput ? 'found' : 'not found'));
                }

                // Show success message using mxMsg
                var successMsg = 'OCR Successful!\n\nDate: ' + (ocrData.date || 'not extracted') + '\nAmount: ' + (ocrData.amount || 'not extracted') + '\n\nDate Confidence: ' + (ocrData.dateConfidence || 0) + '%\nAmount Confidence: ' + (ocrData.amountConfidence || 0) + '%\n\nPlease verify and adjust if needed.';
                console.log('[OCR] Showing success popup');
                showMessage(successMsg, 'success');
            } else {
                var errorMsg = (data.msg || 'OCR processing failed');
                console.log('[OCR] Error response:', data);
                console.log('[OCR] Showing error popup');
                showMessage('OCR: ' + errorMsg, 'error');
            }
        })
        .catch(function(error) {
            console.error('[OCR] Fetch Error:', error);
            hideLoader();
            var errorMsg = 'Error: ' + error.message;
            showMessage(errorMsg, 'error');
        });
    }

    // Function to attach handler to file input
    function attachHandler() {
        console.log('[OCR] Attempting to attach handler...');

        // Try multiple selectors to find the file input
        var selectors = [
            'input[name="billImage"]',
            'input[type="file"][name="billImage"]',
            'li input[type="file"]',
            'input.file-input'
        ];

        var found = false;
        for (var i = 0; i < selectors.length; i++) {
            var inputs = document.querySelectorAll(selectors[i]);
            console.log('[OCR] Selector "' + selectors[i] + '" found', inputs.length, 'elements');

            for (var j = 0; j < inputs.length; j++) {
                if (inputs[j].name === 'billImage' || inputs[j].getAttribute('name') === 'billImage') {
                    console.log('[OCR] Found billImage input, attaching handler');
                    inputs[j].addEventListener('change', handleBillImageChange);
                    found = true;
                }
            }
        }

        if (found) {
            console.log('[OCR] ✓ Handler attached successfully');
        } else {
            console.warn('[OCR] Could not find billImage input, will retry');
            setTimeout(attachHandler, 500);
        }
    }

    // Initialize
    console.log('[OCR] Module loading...');

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[OCR] DOMContentLoaded event fired');
            attachHandler();
        });
    } else {
        console.log('[OCR] DOM already loaded');
        attachHandler();
    }

    // Also try attaching after a short delay to catch dynamically loaded content
    setTimeout(attachHandler, 1000);
})();
