$(document).ready(function() {
    'use strict'
    window.Theme.isRtl = () => {
        return document.body.dir === 'rtl'
    }

    Theme.handleError = (data) => {
        if (typeof data.errors !== 'undefined' && data.errors.length) {
            Theme.handleValidationError(data.errors)
        } else if (typeof data.responseJSON !== 'undefined') {
            if (typeof data.responseJSON.errors !== 'undefined') {
                if (data.status === 422) {
                    Theme.handleValidationError(data.responseJSON.errors)
                }
            } else if (typeof data.responseJSON.message !== 'undefined') {
                Theme.showError(data.responseJSON.message)
            } else {
                $.each(data.responseJSON, (index, el) => {
                    $.each(el, (key, item) => {
                        Theme.showError(item)
                    })
                })
            }
        } else {
            Theme.showError(data.statusText)
        }
    }

    Theme.handleValidationError = (errors) => {
        let message = ''
        $.each(errors, (index, item) => {
            if (message !== '') {
                message += '<br />'
            }
            message += item
        })
        Theme.showError(message)
    }

    $(() => {
        $(document).on('submit', '.newsletter-form, .subscribe-form', function (event) {
            event.preventDefault()
            event.stopPropagation()

            let _self = $(event.target)
            let _btn = _self.find('button[type="submit"]')

            $.ajax({
                type: 'POST',
                cache: false,
                url: _self.closest('form').prop('action'),
                data: new FormData(_self.closest('form')[0]),
                contentType: false,
                processData: false,
                beforeSend: () => {
                    _btn.addClass('button-loading')
                    _btn.attr('disable')
                },
                success: (res) => {
                    if (!res.error) {
                        _self.closest('form').find('input[type=email]').val('')
                        Theme.showSuccess(res.message)
                    } else {
                        Theme.handleError(res.message)
                    }
                },
                error: (res) => {
                    Theme.handleError(res)
                },
                complete: () => {
                    if (typeof refreshRecaptcha !== 'undefined') {
                        refreshRecaptcha()
                    }
                    _btn.removeClass('button-loading')
                    _btn.removeAttr('disable')
                },
            })
        })

        // Dark & light mode
        var rts_light = $('.rts-dark-light')

        if (rts_light.length) {
            var currentTheme = document.documentElement.getAttribute('data-theme') || 'auto'
            var toggle = document.getElementById('rts-data-toggle')
            var storedTheme = localStorage.getItem('echo-theme')

            if (!storedTheme && currentTheme === 'auto') {
                storedTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
            } else if (!storedTheme) {
                storedTheme = currentTheme
            }

            document.documentElement.setAttribute('data-theme', storedTheme)

            toggle.onclick = function () {
                var currentTheme = document.documentElement.getAttribute('data-theme')
                var targetTheme = 'light'

                if (currentTheme === 'light') {
                    targetTheme = 'dark'
                }
                document.documentElement.setAttribute('data-theme', targetTheme)
                localStorage.setItem('echo-theme', targetTheme)
            }
        }
    })
})
