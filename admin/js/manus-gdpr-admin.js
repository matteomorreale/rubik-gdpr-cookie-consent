
// JavaScript per l'area di amministrazione del plugin

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Enhanced consent details toggle
        $('.consent-toggle').on('click', function(e) {
            e.preventDefault();
            
            var consentId = $(this).data('consent-id');
            var details = $('#consent-details-' + consentId);
            var button = $(this);
            
            if (details.is(':visible')) {
                details.slideUp(200);
                button.text(button.data('show-text') || 'Mostra dettagli');
                button.attr('aria-expanded', 'false');
            } else {
                details.slideDown(200);
                button.text(button.data('hide-text') || 'Nascondi dettagli');
                button.attr('aria-expanded', 'true');
            }
        });
        
        // Auto-submit filter form on change
        $('#filter-status').on('change', function() {
            $(this).closest('form').submit();
        });
        
        // Clear search functionality
        $('#consent-search-input').on('input', function() {
            var searchTerm = $(this).val();
            var clearBtn = $('#search-clear');
            
            if (searchTerm.length > 0) {
                if (clearBtn.length === 0) {
                    $(this).after('<button type="button" id="search-clear" class="button" style="margin-left: 5px;">×</button>');
                }
            } else {
                clearBtn.remove();
            }
        });
        
        // Handle clear search button
        $(document).on('click', '#search-clear', function() {
            $('#consent-search-input').val('').focus();
            $(this).remove();
        });
        
        // Keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 75) {
                e.preventDefault();
                $('#consent-search-input').focus();
            }
            
            // Escape to clear search
            if (e.keyCode === 27 && $('#consent-search-input').is(':focus')) {
                $('#consent-search-input').val('');
                $('#search-clear').remove();
            }
        });
        
        // Bulk actions (preparazione per future implementazioni)
        $('.consent-log-bulk-action').on('change', function() {
            var action = $(this).val();
            var $form = $(this).closest('form');
            
            if (action === 'export') {
                // Implementazione futura per esportazione
                console.log('Export selected consents');
            } else if (action === 'delete') {
                // Implementazione futura per eliminazione
                if (confirm('Sei sicuro di voler eliminare i consensi selezionati?')) {
                    console.log('Delete selected consents');
                }
            }
        });
        
        // Statistics cards animation
        $('.consent-stat-card').hover(
            function() {
                $(this).css('transform', 'translateY(-2px)');
                $(this).css('transition', 'transform 0.2s ease');
            },
            function() {
                $(this).css('transform', 'translateY(0)');
            }
        );
        
        // Auto-refresh functionality (optional)
        var autoRefresh = localStorage.getItem('manus-gdpr-auto-refresh');
        if (autoRefresh === 'enabled') {
            setInterval(function() {
                // Solo se l'utente non ha interazioni attive
                if (!$(':focus').length && !$('.consent-details:visible').length) {
                    location.reload();
                }
            }, 300000); // 5 minuti
        }
        
        // Add tooltips to status badges
        $('.consent-badge').each(function() {
            var $badge = $(this);
            var status = $badge.text().toLowerCase();
            var tooltipText = '';
            
            switch(status) {
                case 'accettato':
                    tooltipText = 'L\'utente ha accettato tutti i cookie';
                    break;
                case 'rifiutato':
                    tooltipText = 'L\'utente ha rifiutato tutti i cookie non essenziali';
                    break;
                case 'parziale':
                    tooltipText = 'L\'utente ha accettato solo alcune categorie di cookie';
                    break;
            }
            
            if (tooltipText) {
                $badge.attr('title', tooltipText);
            }
        });
        
    });

})(jQuery);
