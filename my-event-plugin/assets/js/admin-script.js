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
        
        // ===== Photo Selection State =====
        const PhotoSelector = {
            selectedPhotos: [], // Array di oggetti {id, name, thumbnail}
            maxPhotos: 4,
            
            reset: function() {
                this.selectedPhotos = [];
                this.updateUI();
            },
            
            addPhoto: function(photo) {
                if (this.selectedPhotos.length >= this.maxPhotos) {
                    alert('Puoi selezionare massimo ' + this.maxPhotos + ' foto!');
                    return false;
                }
                
                // Verifica che non sia già selezionata
                if (this.isSelected(photo.id)) {
                    return false;
                }
                
                this.selectedPhotos.push(photo);
                this.updateUI();
                return true;
            },
            
            removePhoto: function(photoId) {
                this.selectedPhotos = this.selectedPhotos.filter(p => p.id !== photoId);
                this.updateUI();
            },
            
            isSelected: function(photoId) {
                return this.selectedPhotos.some(p => p.id === photoId);
            },
            
            updateUI: function() {
                const count = this.selectedPhotos.length;
                
                // Aggiorna contatore
                $('.mep-selection-count strong').text(count + '/' + this.maxPhotos);
                
                // Aggiorna campo hidden con gli ID
                const photoIds = this.selectedPhotos.map(p => p.id).join(',');
                $('#selected_photo_ids').val(photoIds);
                
                // Mostra/nascondi sezione foto selezionate
                if (count > 0) {
                    renderSelectedPhotos();
                    $('#mep-selected-photos').slideDown();
                } else {
                    $('#mep-selected-photos').slideUp();
                }
            }
        };
        
        // ===== Gestione Selezione Cartella Google Drive =====
        // Intercetta click sulle cartelle di Use-your-Drive
        $(document).on('click', '#mep-uyd-browser .entry.folder', function(e) {
            // Previeni il comportamento di default solo se clicchiamo direttamente sulla cartella
            const $entry = $(this);
            const folderId = $entry.attr('data-id');
            const folderName = $entry.find('.entry-name-view').text().trim();
            
            console.log('📁 Cartella cliccata:', {
                id: folderId,
                name: folderName,
                entry: $entry
            });
            
            if (!folderId) {
                console.warn('⚠️ ID cartella non trovato. Elemento:', $entry);
                return;
            }
            
            // Popola i campi nascosti
            MEP.folderId.val(folderId);
            MEP.folderName.val(folderName);
            
            // Reset selezione foto precedente
            PhotoSelector.reset();
            
            // Mostra messaggio di caricamento
            MEP.folderValidationMsg
                .removeClass('success error')
                .addClass('validating')
                .html('⏳ Caricamento foto dalla cartella "' + folderName + '"...')
                .slideDown();
            
            // Carica le foto dalla cartella
            loadFolderPhotos(folderId);
            
            // Feedback visivo
            $('#mep-uyd-browser .entry.folder').removeClass('mep-selected-folder');
            $entry.addClass('mep-selected-folder');
            
            // Scroll alla griglia
            setTimeout(function() {
                $('html, body').animate({
                    scrollTop: $('#mep-photo-selector-wrapper').offset().top - 100
                }, 500);
            }, 300);
        });
        
        // Alternativa: intercetta evento Use-your-Drive (se disponibile)
        $(document).on('wpcp-content-loaded', '#mep-uyd-browser', function(e, data) {
            if (data && data.element) {
                const folderId = data.element.attr('data-id');
                const folderPath = data.element.attr('data-path') || data.element.find('.entry-name-view').text().trim();
                
                console.log('📁 Evento Use-your-Drive:', {
                    id: folderId,
                    path: folderPath
                });
                
                if (folderId) {
                    MEP.folderId.val(folderId);
                    MEP.folderName.val(folderPath);
                    PhotoSelector.reset();
                    loadFolderPhotos(folderId);
                }
            }
        });
        
        // ===== Carica Foto dalla Cartella =====
        function loadFolderPhotos(folderId) {
            if (!folderId) return;
            
            // Mostra sezione selector
            $('#mep-photo-selector-wrapper').slideDown();
            
            // Mostra loading
            $('#mep-photo-grid').html('<div class="mep-loading-grid"><span class="mep-spinner"></span><p>Caricamento foto...</p></div>');
            
            // Nascondi eventuali messaggi di validazione precedenti
            MEP.folderValidationMsg.slideUp();
            
            $.ajax({
                url: mepData.ajax_url,
                type: 'POST',
                data: {
                    action: 'mep_get_folder_photos',
                    nonce: mepData.nonce,
                    folder_id: folderId
                },
                success: function(response) {
                    if (response.success && response.data.photos.length > 0) {
                        renderPhotoGrid(response.data.photos);
                        
                        // Mostra messaggio di successo
                        MEP.folderValidationMsg
                            .removeClass('error')
                            .addClass('success')
                            .html('✓ Trovate ' + response.data.photos.length + ' foto. Seleziona 4 immagini dalla griglia sottostante.')
                            .slideDown();
                    } else {
                        $('#mep-photo-grid').html('<div class="mep-loading-grid"><p style="color: #d63638;">❌ Nessuna foto trovata in questa cartella.</p></div>');
                        
                        MEP.folderValidationMsg
                            .removeClass('success')
                            .addClass('error')
                            .html('❌ Nessuna foto trovata nella cartella selezionata. Scegli una cartella diversa.')
                            .slideDown();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Errore AJAX completo:', {
                        xhr: xhr,
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        response: xhr.responseJSON
                    });
                    
                    let errorMessage = 'Errore sconosciuto';
                    let debugInfo = '';
                    
                    if (xhr.responseJSON && xhr.responseJSON.data) {
                        errorMessage = xhr.responseJSON.data.message || errorMessage;
                        debugInfo = xhr.responseJSON.data.debug || '';
                    }
                    
                    $('#mep-photo-grid').html(
                        '<div class="mep-loading-grid">' +
                        '<p style="color: #d63638; margin-bottom: 10px;">❌ ' + errorMessage + '</p>' +
                        (debugInfo ? '<p style="color: #666; font-size: 12px;">Debug: ' + debugInfo + '</p>' : '') +
                        '<button class="button" onclick="location.reload()">Ricarica Pagina</button>' +
                        '</div>'
                    );
                    
                    MEP.folderValidationMsg
                        .removeClass('success')
                        .addClass('error')
                        .html('❌ ' + errorMessage + (debugInfo ? '<br><small>(' + debugInfo + ')</small>' : ''))
                        .slideDown();
                }
            });
        }
        
        // ===== Renderizza Griglia Foto =====
        function renderPhotoGrid(photos) {
            const grid = $('#mep-photo-grid');
            grid.empty();
            
            photos.forEach(function(photo) {
                const photoItem = $('<div>')
                    .addClass('mep-photo-item')
                    .attr('data-photo-id', photo.id)
                    .attr('data-photo-name', photo.name)
                    .html(`
                        <img src="${photo.thumbnail}" alt="${photo.name}">
                        <div class="mep-photo-check">✓</div>
                        <div class="mep-photo-name">${photo.name}</div>
                    `);
                
                // Gestisci click
                photoItem.on('click', function() {
                    const photoId = $(this).attr('data-photo-id');
                    const photoName = $(this).attr('data-photo-name');
                    const thumbnail = $(this).find('img').attr('src');
                    
                    if ($(this).hasClass('selected')) {
                        // Deseleziona
                        $(this).removeClass('selected');
                        PhotoSelector.removePhoto(photoId);
                    } else {
                        // Seleziona
                        if (PhotoSelector.addPhoto({
                            id: photoId,
                            name: photoName,
                            thumbnail: thumbnail
                        })) {
                            $(this).addClass('selected');
                        }
                    }
                });
                
                grid.append(photoItem);
            });
        }
        
        // ===== Renderizza Foto Selezionate =====
        function renderSelectedPhotos() {
            const list = $('#mep-selected-photos-list');
            list.empty();
            
            PhotoSelector.selectedPhotos.forEach(function(photo, index) {
                const preview = $('<div>')
                    .addClass('mep-selected-photo-preview')
                    .html(`
                        <img src="${photo.thumbnail}" alt="${photo.name}">
                        <div class="mep-photo-number">${index + 1}</div>
                        <div class="mep-remove-photo" data-photo-id="${photo.id}">×</div>
                    `);
                
                // Gestisci rimozione
                preview.find('.mep-remove-photo').on('click', function(e) {
                    e.stopPropagation();
                    const photoId = $(this).attr('data-photo-id');
                    
                    // Rimuovi dalla selezione
                    PhotoSelector.removePhoto(photoId);
                    
                    // Rimuovi classe selected dalla griglia
                    $('.mep-photo-item[data-photo-id="' + photoId + '"]').removeClass('selected');
                });
                
                list.append(preview);
            });
        }
        
        // Rimuovo questa funzione perché non serve più
        // La gestiamo direttamente in loadFolderPhotos()
        
        // ===== Submit Form =====
        MEP.form.on('submit', function(e) {
            e.preventDefault();
            
            // Validazione base
            if (!MEP.folderId.val()) {
                alert(mepData.strings.select_folder);
                return false;
            }
            
            // Validazione foto selezionate
            if (PhotoSelector.selectedPhotos.length !== 4) {
                alert('Devi selezionare esattamente 4 foto!');
                $('html, body').animate({
                    scrollTop: $('#mep-photo-selector-wrapper').offset().top - 100
                }, 500);
                return false;
            }
            
            // Validazione featured image
            const featuredIndex = $('#mep-featured-image-select').val();
            if (featuredIndex === '') {
                alert('Devi scegliere quale foto usare come immagine di copertina!');
                $('html, body').animate({
                    scrollTop: $('#mep-featured-image-select').offset().top - 100
                }, 500);
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
                        
                        // Crea la lista degli URL delle foto importate
                        let photoUrlsHtml = '';
                        if (response.data.photo_urls && response.data.photo_urls.length > 0) {
                            photoUrlsHtml = '<div style="margin-top: 20px; padding: 15px; background: #f0f6fc; border-radius: 4px; border-left: 4px solid #2271b1;">' +
                                '<h4 style="margin-top: 0;">📋 URL delle foto importate (copia per il tuo HTML):</h4>' +
                                '<div style="display: grid; gap: 10px;">';
                            
                            response.data.photo_urls.forEach(function(url, index) {
                                const isCover = (index == response.data.featured_index);
                                photoUrlsHtml += '<div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #c3c4c7;">' +
                                    '<strong>Foto ' + (index + 1) + (isCover ? ' (Copertina)' : '') + ':</strong><br>' +
                                    '<input type="text" value="' + url + '" readonly onclick="this.select()" style="width: 100%; padding: 5px; margin-top: 5px; font-family: monospace; font-size: 12px;">' +
                                    '</div>';
                            });
                            
                            photoUrlsHtml += '</div></div>';
                        }
                        
                        MEP.statusMsg
                            .removeClass('processing')
                            .addClass('success')
                            .html(
                                mepData.strings.success + '<br>' +
                                photoUrlsHtml +
                                '<div style="margin-top: 15px;">' +
                                '<a href="' + response.data.edit_url + '" class="button button-primary" style="margin-top: 10px;">Modifica Evento</a> ' +
                                '<a href="' + response.data.view_url + '" class="button" style="margin-top: 10px;" target="_blank">Visualizza</a>' +
                                '</div>'
                            );
                        
                        // Scroll to message
                        $('html, body').animate({
                            scrollTop: MEP.statusMsg.offset().top - 100
                        }, 500);
                        
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
        
        // Debug: monitora quando appare Use-your-Drive
        setTimeout(function() {
            const uydContainer = $('#mep-uyd-browser');
            const hasUYD = uydContainer.find('.useyourdrive').length > 0;
            const folderCount = uydContainer.find('.entry.folder').length;
            
            console.log('📊 Use-your-Drive Status:', {
                container: uydContainer.length > 0 ? 'Trovato' : 'NON trovato',
                plugin_loaded: hasUYD ? 'Sì' : 'No',
                folders_visible: folderCount,
                instructions: 'Clicca su una cartella per caricare le foto'
            });
            
            if (!hasUYD) {
                console.warn('⚠️ Use-your-Drive non si è caricato correttamente. Controlla la console per errori.');
            }
        }, 2000);
    });
    
})(jQuery);
