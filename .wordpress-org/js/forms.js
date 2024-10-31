jQuery(function($) {

    var spinners = {};

    function enable_spinner(identifier, selector) {
        if ( typeof selector === 'undefined' )
            selector = '#' + identifier;

        spinners[identifier] = $(selector);
        spinners[identifier].css('display', 'inline');
    }

    function disable_spinner(identifier) {
        if ( spinners[identifier] )
            spinners[identifier].css('display', 'none');
    }

    function append_to_editor(text) {
        var mce = typeof(tinymce) != 'undefined',
            html_ed = $('.wp-editor-area:visible');

        if ( mce && tinymce.activeEditor ) {
            ed = tinymce.activeEditor;
            ed.setContent(ed.getContent() + text);
        } else if( html_ed.size() != 0 ) {
            html_ed.val(html_ed.val() + text);
        }
    }

    function generate_shortcode(type, dialog) {
        var attributes = {
            boolean: ['required', 'nowrap', 'use_ssl'],
            text: ['name', 'label', 'placeholder', 'id', 'class', 'private_key', 'public_key'],
            select: ['theme']
        };
        var tokens = [type],
            key, value;

        for (var i = 0; i < attributes.text.length; i++ ) {
            key = attributes.text[i];
            value = $('input[name=' + key + ']:visible', dialog).val();
            if (value) {
                tokens.push( key + '="' + value + '"' );
            }
        }

        for (var i = 0; i < attributes.select.length; i++ ) {
            key = attributes.select[i];
            value = $('select[name=' + key + ']:visible option:selected', dialog).val();
            if (value) {
                tokens.push( key + '="' + value + '"' );
            }
        }

        for (var i = 0; i < attributes.boolean.length; i++ ) {
            key = attributes.boolean[i];
            if ($('input[name=' + key + ']:visible', dialog).prop('checked')) {
                tokens.push( key );
            }
        }

        return "[" + tokens.join(' ') + "]"
    }

    // initialize name inputs with random names
    function set_custom_field_rand_name( dialog ) {
        var type = $(dialog).attr('data-type');
        name = type + '-' + Math.floor(Math.random() * 10000);
        $('input[name=name]', dialog).val(name);
    }

    $('.custom-field-dialog').each(function(k, dialog) {
        set_custom_field_rand_name(dialog);
    });

    $('#captcha-field input[name=use_global]').change(function(event) {
        var checked = $(this).prop('checked'),
            dialog = $(this).parents('.custom-field-dialog'),
            key_fields = $('input[name=private_key], input[name=public_key], input[name=use_ssl]', dialog);

        if (checked) {
            $(key_fields)
                .val('')
                .parents('.input')
                    .hide();
        } else {
            $(key_fields).parents('.input').show();
        }
        event.preventDefault();
    });

    $('.custom-field-dialog button').click(function(event) {
        var dialog = $(this).parents('.custom-field-dialog'),
            type = $(dialog).attr('data-type'),
            shortcode = generate_shortcode( type, dialog );

        append_to_editor( "\n" + shortcode );
        window.parent.tb_remove();
        set_custom_field_rand_name(dialog);
        event.preventDefault();
    });

    // Title placeholder
    $('#title').blur(function() {
        if ($(this).val() == '') {
            $('#titlediv label').removeClass('screen-reader-text');
        }
    })

    $('#title').focus(function() {
        $('#titlediv label').addClass('screen-reader-text');
    });


    function set_settings_toggle(checkbox_sel, divs_sel) {
        if (!$(checkbox_sel).prop('checked')) {
            $(divs_sel).hide();
        }
        $(checkbox_sel).change(function(e) {
            var checked = $(checkbox_sel).prop('checked'),
                divs = $(divs_sel);

            if (checked) {
                $(divs).show();
            } else {
                $(divs).hide();
            }

            e.preventDefault();
        });
    }

    set_settings_toggle(
        '#auto-enabled-check input[type=checkbox]',
        $('#autoresponse-div').find('.half-left, .half-right')
    );

    set_settings_toggle(
        '#sg-enabled-check input[type=checkbox]',
        $('#form-salesgoals-settings').find('.status, #sg-get-auth')
    );

    $('#get-auth-key').click(function(e) {
        var id = $('input[name=ID]').val(),
            user = $('input[name=sg_username]').val(),
            self = this;
        enable_spinner('get-auth-key-spinner');
        $(this).prop('disabled', true);
        $.post(
            ajaxurl,
            {
                action: 'get_auth_key',
                form_id: id,
                username: user,
                password: $('input[name=sg_password]').val()
            },
            function(response){
                response = $.parseJSON(response);
                if ( response.success ) {
                    $('#sg-warning').hide();
                    $('#sg-success').show();
                    $('#sg-success .account').text(user);
                    $('#sg-success .plan').text(response.billing_status);
                    $('#sg-get-auth').hide();
                    $('input[name=sg_auth_key]').remove();
                    $('input[name=sg_auth_user]').remove();
                    $('input[name=sg_billing_status]').remove();
                    $('#form-salesgoals-settings').append($('<input type="hidden" name="sg_auth_key" value="'+response.auth_key+'" />'))
                    $('#form-salesgoals-settings').append($('<input type="hidden" name="sg_auth_user" value="'+user+'" />'));
                    $('#form-salesgoals-settings').append($('<input type="hidden" name="sg_billing_status" value="'+response.billing_status+'" />'));
                } else {
                    alert(response.message);
                }
                disable_spinner('get-auth-key-spinner');
                $(self).prop('disabled', false);
            }
        );
        e.preventDefault();
    });

    $('#copy-auth-key').click(function(e) {
        var to_id = $('input[name=ID]').val(),
            from_id = $('select[name=sg_copy_form]').val(),
            self = this;
        enable_spinner('copy-auth-key-spinner');
        $(this).prop('disabled', true);
        $.post(
            ajaxurl,
            {
                action: 'copy_auth_key',
                from: from_id,
                to: to_id
            },
            function(response){
                response = $.parseJSON(response);
                if ( response.success ) {
                    $('#sg-warning').hide();
                    $('#sg-success').show();
                    $('#sg-success .account').text(response.user);
                    $('#sg-get-auth').hide();
                    $('input[name=sg_auth_key]').remove();
                    $('input[name=sg_auth_user]').remove();
                    $('input[name=sg_billing_status]').remove();
                    $('#form-salesgoals-settings').append($('<input type="hidden" name="sg_auth_key" value="'+response.auth_key+'" />'))
                    $('#form-salesgoals-settings').append($('<input type="hidden" name="sg_auth_user" value="'+response.user+'" />'));
                    $('#form-salesgoals-settings').append($('<input type="hidden" name="sg_billing_status" value="'+response.billing_status+'" />'));
                } else {
                    alert(response.message);
                }
                disable_spinner('copy-auth-key-spinner');
                $(self).prop('disabled', false);
            }
        );
        e.preventDefault();
    });

    $('#remove-auth-key').click(function(e) {

        function remove_form_elems() {
            $('#sg-success').hide();
            $('#sg-warning').show();
            $('#sg-get-auth').show();
            $('input[name=sg_auth_key]').remove();
            $('input[name=sg_auth_user]').remove();
        }

        var id = $('input[name=ID]').val(),
            self = this;
        if ( id ) {
            enable_spinner('remove-auth-key-spinner');
            $(this).prop('disabled', true);
            $.post(
                ajaxurl,
                {
                    action: 'remove_auth_key',
                    form: id
                },
                function(response){
                    response = $.parseJSON(response);
                    console.log(response);
                    if ( response.success ) {
                        remove_form_elems();
                    } else {
                        alert(response.message);
                    }
                    disable_spinner('remove-auth-key-spinner');
                    $(self).prop('disabled', false);
                }
            );
        } else {
            remove_form_elems();
        }
        e.preventDefault();
    });

    $('.custom-field-dialog .input .help-tip')
        .click(function(e) { e.preventDefault() })
        .tipTip({delay: 0});

});