/**
 * app.js — global frontend logic (jQuery + Bootstrap 5)
 */
(function ($) {
    'use strict';

    $(document).on('click', '.toggle-pw', function () {
        var $btn   = $(this);
        var $input = $($btn.data('target'));
        var isText = $input.attr('type') === 'text';
        $input.attr('type', isText ? 'password' : 'text');
        var t = window.APP_TRANSLATIONS || {};
        var txtShow = t.show_password || 'Show';
        var txtHide = t.hide_password || 'Hide';
        $btn.text(isText ? txtShow : txtHide);
    });


    function apiPost(url, data) {
        return $.ajax({ url: url, method: 'POST', data: data, dataType: 'json' });
    }

    function apiGet(url, params) {
        return $.ajax({ url: url, method: 'GET', data: params || {}, dataType: 'json' });
    }

    function clearErrors($form) {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.field-error').remove();
    }

    function showFieldErrors($form, errors) {
        clearErrors($form);
        $.each(errors, function (field, msg) {
            var $input = $form.find('[name="' + field + '"]');
            $input.addClass('is-invalid');
            var $anchor = $input.closest('.input-group').length
                ? $input.closest('.input-group')
                : $input;
            $('<div class="invalid-feedback field-error">').text(msg).insertAfter($anchor);
        });
    }

    function showAlert($form, msg, type) {
        type = type || 'danger';
        $('<div class="alert alert-' + type + ' alert-dismissible fade show mt-2" role="alert">')
            .html(escapeHtml(msg) + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>')
            .prependTo($form);
    }

    function safeJson(str) {
        try { return JSON.parse(str); } catch (e) { return null; }
    }

    function escapeHtml(str) {
        return $('<div>').text(str).html();
    }

    // -----------------------------------------------------------------------
    // Login form
    // -----------------------------------------------------------------------
    var $loginForm = $('#login-form');
    if ($loginForm.length) {
        $loginForm.on('submit', function (e) {
            e.preventDefault();
            clearErrors($loginForm);

            var $btn = $('#btn-login'), $sp = $('#login-spinner');
            $btn.prop('disabled', true);
            $sp.removeClass('d-none');

            apiPost('/api/login.php', $loginForm.serialize())
                .done(function (res) {
                    if (res.redirect) { window.location.href = res.redirect; }
                })
                .fail(function (xhr) {
                    var res = safeJson(xhr.responseText);
                    if (res && res.errors) { showFieldErrors($loginForm, res.errors); }
                    else { showAlert($loginForm, (res && res.message) || 'Login failed.'); }
                })
                .always(function () { $btn.prop('disabled', false); $sp.addClass('d-none'); });
        });
    }

    // -----------------------------------------------------------------------
    // Register form + live password strength
    // -----------------------------------------------------------------------
    var $registerForm = $('#register-form');
    if ($registerForm.length) {

        $('#password').on('input', function () {
            var lvls = [
                { pct: 0,   cls: '',           text: '' },
                { pct: 25,  cls: 'bg-danger',  text: 'Weak' },
                { pct: 50,  cls: 'bg-warning', text: 'Fair' },
                { pct: 75,  cls: 'bg-info',    text: 'Good' },
                { pct: 100, cls: 'bg-success', text: 'Strong' },
            ];
            var lvl = lvls[passwordStrength($(this).val())];
            $('#pw-strength-bar').css('width', lvl.pct + '%')
                                 .attr('class', 'progress-bar ' + lvl.cls);
            $('#pw-strength-label').text(lvl.text);
        });

        $('#password_confirm').on('input', function () {
            var match = $(this).val() === $('#password').val();
            $(this).toggleClass('is-invalid', !match && $(this).val().length > 0);
        });

        $registerForm.on('submit', function (e) {
            e.preventDefault();
            clearErrors($registerForm);

            var $btn = $('#btn-register'), $sp = $('#register-spinner');
            $btn.prop('disabled', true);
            $sp.removeClass('d-none');

            apiPost('/api/register.php', $registerForm.serialize())
                .done(function (res) {
                    showAlert($registerForm, res.message || 'Registration successful!', 'success');
                    $registerForm[0].reset();
                    $('#pw-strength-bar').css('width', '0%').attr('class', 'progress-bar');
                    $('#pw-strength-label').text('');
                    setTimeout(function () { window.location.href = '/login.php'; }, 2000);
                })
                .fail(function (xhr) {
                    var res = safeJson(xhr.responseText);
                    if (res && res.errors) { showFieldErrors($registerForm, res.errors); }
                    else { showAlert($registerForm, (res && res.message) || 'Registration failed.'); }
                })
                .always(function () { $btn.prop('disabled', false); $sp.addClass('d-none'); });
        });
    }

    // -----------------------------------------------------------------------
    // Dashboard quick-test buttons
    // -----------------------------------------------------------------------
    function runTest(action) {
        var $out = $('#test-result').removeClass('d-none').text('Loading…');
        apiGet('/api/health.php', { action: action })
            .done(function (res) { $out.text(JSON.stringify(res, null, 2)); })
            .fail(function (xhr) { $out.text(xhr.responseText || 'Request failed'); });
    }

    $('#btn-db-test').on('click',    function () { runTest('db_test'); });
    $('#btn-redis-test').on('click', function () { runTest('redis_test'); });

    // -----------------------------------------------------------------------
    // Password strength scorer  (returns 0–4)
    // -----------------------------------------------------------------------
    function passwordStrength(pw) {
        if (!pw || pw.length < 4) { return 0; }
        var score = 0;
        if (pw.length >= 8)                          { score++; }
        if (pw.length >= 12)                         { score++; }
        if (/[A-Z]/.test(pw) && /[a-z]/.test(pw))   { score++; }
        if (/[0-9]/.test(pw))                        { score++; }
        if (/[^A-Za-z0-9]/.test(pw))                 { score++; }
        return Math.min(4, score);
    }

}(jQuery));
