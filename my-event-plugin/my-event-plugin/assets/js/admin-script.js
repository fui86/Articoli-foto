/**
 * My Event Plugin - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // ===== Inizializzazione =====
        const MEP = {
            form: $('#mep-event-form'),
            submitBtn: $('#mep-submit-btn'),
            statusMsg: $('#mep-status-message'),
            folderValidationMsg: $('#mep-folder-validation-message'),
            
            // Campi folder
            folderId: $('#event_folder_id'),
            folderAccount: $('#event_folder_account'),
            folderName: $('#event_folder_name'),
            
            // Counter SEO
            seoTitle: $('#seo_title'),
            seoDescription: $('#seo_description'),
            seoCounter: $('.seo-counter'),
            descCounter: $('.desc-counter')
        };
        
        // ===== Auto-popolamento Titolo SEO =====
        $('#event_title').on('input', function() {
            if (MEP.seoTitle.val() === '') {
                MEP.seoTitle.val($(this).val());
                updateSeoCounter();
            }
        });
        
        // ===== Counter SEO Title =====
        MEP.seoTitle.on('input', updateSeoCounter);
        function updateSeoCounter() {
            const length = MEP.seoTitle.val().length;
            const counter = MEP.seoCounter;
            
            counter.text(length + '/60');
            
            if (length > 60) {
                counter.addClass('danger').removeClass('warning');
            } else if (length > 50) {
                counter.addClass('warning').removeClass('danger');
            } else {
                counter.removeClass('warning danger');
            }
        }
        
        // ===== Counter Meta Description =====
        MEP.seoDescription.on('input', updateDescCounter);
        function updateDescCounter() {
            const length = MEP.seoDescription.val().length;
            const counter = MEP.descCounter;
            
            counter.text(length + '/160');
            
            if (length > 160) {
                counter.addClass('danger').removeClass('warning');
            } else if (length > 140) {
                counter.addClass('warning').removeClass('danger');
            } else {
                counter.removeClass('warning danger');
            }
        }
        
        // ===== Gestione Selezione Cartella Google Drive =====
        // Use-your-Drive emette questo evento quando una cartella viene selezionata
        $('.wpcp-module').on('wpcp-content-loaded', function(e, data) {
            const folderId = data.element.attr('data-id');
            const folderPath = data.element.attr('data-path');
            const accountId = data.element.attr('data-account-id');
            
            console.log('📁 Cartella selezionata:', {
                id: folderId,
                path: folderPath,
                account: accountId
            });
            
            // Popola i campi nascosti
            MEP.folderId.val(folderId);
            MEP.folderAccount.val(accountId);
            MEP.folderName.val(folderPath);
            
            // Valida la cartella (verifica numero immagini)
            validateFolder(folderId);
        });
        
        // ===== Validazione Cartella =====
        function validateFolder(folderId) {
            if (!folderId) return;
            
            showValidationMessage('validating', mepData.strings.validating);
            
            $.ajax({
                url: mepData.ajax_url,
                type: 'POST',
                data: {
                    action: 'mep_validate_folder',
                    nonce: mepData.nonce,
                    folder_id: folderId
                },
                success: function(response) {
                    if (response.success) {
                        showValidationMessage('success', response.data.message);
                    } else {
                        showValidationMessage('error', response.data.message);
                    }
                },
                error: function() {
                    showValidationMessage('error', 'Errore di connessione durante la validazione');
                }
            });
        }
        
        function showValidationMessage(type, message) {
            MEP.folderValidationMsg
                .removeClass('success error')
                .addClass(type)
                .html(message)
                .slideDown();
        }
        
        // ===== Submit Form =====
        MEP.form.on('submit', function(e) {
            e.preventDefault();
            
            // Validazione base
            if (!MEP.folderId.val()) {
                alert(mepData.strings.select_folder);
                return false;
            }
            
            // Disabilita submit button
            MEP.submitBtn.prop('disabled', true);
            
            // Mostra messaggio processing
            MEP.statusMsg
                .removeClass('success error')
                .addClass('processing')
                .html('<span class="mep-spinner"></span> ' + mepData.strings.processing)
                .show();
            
            // Prepara dati
            const formData = MEP.form.serialize() + '&action=mep_process_event_creation&nonce=' + mepData.nonce;
            
            // Invia via AJAX
            $.ajax({
                url: mepData.ajax_url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Successo!
                        MEP.statusMsg
                            .removeClass('processing')
                            .addClass('success')
                            .html(
                                mepData.strings.success + '<br>' +
                                '<a href="' + response.data.edit_url + '" class="button button-primary" style="margin-top: 10px;">Modifica Evento</a> ' +
                                '<a href="' + response.data.view_url + '" class="button" style="margin-top: 10px;" target="_blank">Visualizza</a>'
                            );
                        
                        // Reset form dopo 3 secondi
                        setTimeout(function() {
                            if (confirm('Evento creato! Vuoi crearne un altro?')) {
                                location.reload();
                            } else {
                                window.location.href = response.data.edit_url;
                            }
                        }, 3000);
                        
                    } else {
                        // Errore
                        MEP.statusMsg
                            .removeClass('processing')
                            .addClass('error')
                            .html(mepData.strings.error + ': ' + response.data.message);
                        
                        MEP.submitBtn.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {xhr, status, error});
                    
                    MEP.statusMsg
                        .removeClass('processing')
                        .addClass('error')
                        .html(mepData.strings.error + ': Errore di connessione. Riprova.');
                    
                    MEP.submitBtn.prop('disabled', false);
                }
            });
            
            return false;
        });
        
        // ===== Conferma prima di lasciare la pagina se il form è compilato =====
        let formChanged = false;
        MEP.form.find('input, textarea, select').on('change', function() {
            formChanged = true;
        });
        
        $(window).on('beforeunload', function(e) {
            if (formChanged && !MEP.submitBtn.prop('disabled')) {
                const message = 'Hai modifiche non salvate. Sei sicuro di voler uscire?';
                e.returnValue = message;
                return message;
            }
        });
        
        // ===== Helper: Smooth Scroll to Error =====
        function scrollToError(element) {
            $('html, body').animate({
                scrollTop: element.offset().top - 100
            }, 500);
        }
        
        // ===== Tooltip Helper (opzionale) =====
        $('.mep-label[title]').each(function() {
            $(this).attr('data-tooltip', $(this).attr('title')).removeAttr('title');
        });
        
        // ===== Log per debug =====
        console.log('🚀 My Event Plugin - Admin Script caricato');
        console.log('Config:', mepData);
    });
    
})(jQuery);
