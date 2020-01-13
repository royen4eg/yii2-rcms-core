"use strict";

(function ($) {
    const regexID = /^(.+?)(-(\d+-)+(\w+-)*)(.+)$/i;
    const regexName = /^(.+?)((\[\d+])+(\[\w+])*)(\[.+]$)/i;

    $.fn.rcmsDynamicForm = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.rcmsDynamicForm');
            return false;
        }
    };

    const events = {
        beforeInsert: 'beforeInsert',
        afterInsert: 'afterInsert',
        beforeDelete: 'beforeDelete',
        afterDelete: 'afterDelete',
        limitReached: 'limitReached'
    };

    let methods = {
        init: function (widgetOptions) {
            return this.each(function () {
                widgetOptions.template = _parseTemplate(widgetOptions);
            });
        },

        addItem: function (widgetOptions, e, $elem) {
            _addItem(widgetOptions, e, $elem);
        },

        deleteItem: function (widgetOptions, e, $elem) {
            _deleteItem(widgetOptions, e, $elem);
        },

        moveBackward: function (widgetOptions, e, $elem) {
            _swapItem(widgetOptions, e, $elem, false);
        },

        moveForward: function (widgetOptions, e, $elem) {
            _swapItem(widgetOptions, e, $elem, true);
        },

        updateContainer: function () {
            const widgetOptions = eval($(this).attr('data-dynamicform'));
            _updateAttributes(widgetOptions);
            _restoreSpecialJs(widgetOptions);
            _fixFormValidaton(widgetOptions);
        }
    };

    const _parseTemplate = function (widgetOptions) {
        let $template = $(widgetOptions.template);
        $template.find('div[data-dynamicform]').each(function () {
            let widgetOptions = eval($(this).attr('data-dynamicform'));
            if ($(widgetOptions.widgetItem).length > 1) {
                const item = $(this).find(widgetOptions.widgetItem).first()[0].outerHTML;
                $(this).find(widgetOptions.widgetBody).html(item);
            }
        });

        $template.find('input, textarea, select').each(function () {
            if ($(this).is(':checkbox') || $(this).is(':radio')) {
                const type = ($(this).is(':checkbox')) ? 'checkbox' : 'radio';
                const inputName = $(this).attr('name');
                const count = $template.find('input[type="' + type + '"][name="' + inputName + '"]').length;
                let $inputHidden = $template.find('input[type="hidden"][name="' + inputName + '"]').first();

                if ($inputHidden && count === 1) {
                    $(this).val(1);
                    $inputHidden.val(0);
                }

                $(this).prop('checked', false);
            } else if ($(this).is('select')) {
                $(this).find('option:selected').removeAttr("selected");
            } else {
                $(this).val('');
            }
        });

        // remove "error/success" css class
        let yiiActiveFormData = $('#' + widgetOptions.formId).yiiActiveForm('data');
        $template.find('.' + yiiActiveFormData.settings.errorCssClass).removeClass(yiiActiveFormData.settings.errorCssClass);
        $template.find('.' + yiiActiveFormData.settings.successCssClass).removeClass(yiiActiveFormData.settings.successCssClass);

        return $template;
    };

    const _getWidgetOptionsRoot = function (widgetOptions) {
        return eval($(widgetOptions.widgetBody).parents('div[data-dynamicform]').last().attr('data-dynamicform'));
    };

    const _getLevel = function ($elem) {
        let level = $elem.parents('div[data-dynamicform]').length;
        level = (level < 0) ? 0 : level;
        return level;
    };

    const _count = function ($elem, widgetOptions) {
        return $elem.closest('.' + widgetOptions.widgetContainer).find(widgetOptions.widgetItem).length;
    };

    const _createIdentifiers = function (level) {
        return new Array(level + 2).join('0').split('');
    };

    const _addItem = function (widgetOptions, e, $elem) {
        const count = _count($elem, widgetOptions);

        if (count < widgetOptions.limit) {
            const $origin = widgetOptions.template;
            const $newClone = $origin.clone(false, false);

            if (widgetOptions.insertPosition === 'top') {
                $elem.closest('.' + widgetOptions.widgetContainer).find(widgetOptions.widgetBody).prepend($newClone);
            } else {
                $elem.closest('.' + widgetOptions.widgetContainer).find(widgetOptions.widgetBody).append($newClone);
            }

            _updateAttributes(widgetOptions);
            _restoreSpecialJs(widgetOptions);
            _fixFormValidaton(widgetOptions);
            $elem.closest('.' + widgetOptions.widgetContainer).triggerHandler(events.afterInsert, $newClone);
        } else {
            // trigger a custom event for hooking
            $elem.closest('.' + widgetOptions.widgetContainer).triggerHandler(events.limitReached, widgetOptions.limit);
        }
    };

    const _removeValidations = function ($elem, widgetOptions, count) {
        if (count > 1) {
            $elem.find('div[data-dynamicform]').each(function () {
                const currentWidgetOptions = eval($(this).attr('data-dynamicform'));
                const level = _getLevel($(this));
                const identifiers = _createIdentifiers(level);
                const numItems = $(this).find(currentWidgetOptions.widgetItem).length;

                for (let i = 1; i <= numItems - 1; i++) {
                    let aux = identifiers;
                    aux[level] = i;
                    currentWidgetOptions.fields.forEach(function (input) {
                        var id = input.id.replace("{}", aux.join('-'));
                        let form = $("#" + currentWidgetOptions.formId);
                        if (form.yiiActiveForm("find", id) !== "undefined") {
                            form.yiiActiveForm("remove", id);
                        }
                    });
                }
            });

            const level = _getLevel($elem.closest('.' + widgetOptions.widgetContainer));
            const widgetOptionsRoot = _getWidgetOptionsRoot(widgetOptions);
            let identifiers = _createIdentifiers(level);
            identifiers[0] = $(widgetOptionsRoot.widgetItem).length - 1;
            identifiers[level] = count - 1;

            widgetOptions.fields.forEach(function (input) {
                const id = input.id.replace("{}", identifiers.join('-'));
                let form = $("#" + widgetOptions.formId);
                if (form.yiiActiveForm("find", id) !== "undefined") {
                    form.yiiActiveForm("remove", id);
                }
            });
        }
    };

    const _deleteItem = function (widgetOptions, e, $elem) {
        const count = _count($elem, widgetOptions);

        if (count > widgetOptions.min) {
            let $todelete = $elem.closest(widgetOptions.widgetItem);

            // trigger a custom event for hooking
            let container =  $('.' + widgetOptions.widgetContainer);
            const eventResult = container.triggerHandler(events.beforeDelete, $todelete);
            if (eventResult !== false) {
                _removeValidations($todelete, widgetOptions, count);
                $todelete.remove();
                _updateAttributes(widgetOptions);
                _restoreSpecialJs(widgetOptions);
                _fixFormValidaton(widgetOptions);
                container.triggerHandler(events.afterDelete);
            }
        }
    };

    const _swapItem = function (widgetOptions, e, $elem, forward) {
        let block = $elem.closest(widgetOptions.widgetItem)[0];
        if(forward){
            if(block.nextElementSibling){
                block.parentNode.insertBefore(block.nextElementSibling, block);
            }
        } else {
            if(block.previousElementSibling){
                block.parentNode.insertBefore(block, block.previousElementSibling);
            }
        }
        _updateAttributes(widgetOptions);
        _restoreSpecialJs(widgetOptions);
        _fixFormValidaton(widgetOptions);
    };

    const _updateAttrID = function ($elem, index) {
        const widgetOptions = eval($elem.closest('div[data-dynamicform]').attr('data-dynamicform'));
        const id = $elem.attr('id');
        let newID = id;

        if (id !== undefined) {
            let matches = id.match(regexID);
            if (matches && matches.length >= 4) {
                matches[2] = matches[2].substring(1, matches[2].length - 1);
                let identifiers = matches[2].split('-');
                identifiers[0] = index;

                if (identifiers.length > 1) {
                    for (let i = identifiers.length - 1; i >= 1; i--) {
                        identifiers[i];
                    }

                    let widgetsOptions = [];
                    $elem.parents('div[data-dynamicform]').each(function (i) {
                        widgetsOptions[i] = eval($(this).attr('data-dynamicform'));
                    });

                    widgetsOptions = widgetsOptions.reverse();
                    let y = widgetsOptions.length - 1;
                    for (let i = identifiers.length - 1; i >= 1; i--) {
                        if(identifiers[i].match(/^\d+$/)){
                            identifiers[i] = $elem.closest(widgetsOptions[y].widgetItem).index();
                            y--;
                        }
                    }
                }

                newID = matches[1] + '-' + identifiers.join('-') + '-' + matches[matches.length - 1];
                $elem.attr('id', newID);
            } else {
                newID = id + index;
                $elem.attr('id', newID);
            }
        }

        if (id !== newID) {
            $elem.closest(widgetOptions.widgetItem).find('.field-' + id).each(function () {
                $(this).removeClass('field-' + id).addClass('field-' + newID);
            });
            // update "for" attribute
            $elem.closest(widgetOptions.widgetItem).find("label[for='" + id + "']").attr('for', newID);
        }

        return newID;
    };

    const _updateAttrName = function ($elem, index) {
        let name = $elem.attr('name');

        if (name !== undefined) {
            let matches = name.match(regexName);

            if (matches && matches.length >= 4) {
                matches[2] = matches[2].replace(/]\[/g, "-").replace(/[\]\[]/g, '');
                let identifiers = matches[2].split('-');
                identifiers[0] = index;

                if (identifiers.length > 1) {
                    let widgetsOptions = [];
                    $elem.parents('div[data-dynamicform]').each(function (i) {
                        widgetsOptions[i] = eval($(this).attr('data-dynamicform'));
                    });

                    widgetsOptions = widgetsOptions.reverse();
                    let y = widgetsOptions.length - 1;
                    for (let i = identifiers.length - 1; i >= 1; i--) {
                        if(identifiers[i].match(/^\d+$/)){
                            identifiers[i] = $elem.closest(widgetsOptions[y].widgetItem).index();
                            y--;
                        }
                    }
                }

                name = matches[1] + '[' + identifiers.join('][') + ']' + matches[matches.length - 1];
                $elem.attr('name', name);
            }
        }

        return name;
    };

    const _updateAttributes = function (widgetOptions) {
        const widgetOptionsRoot = _getWidgetOptionsRoot(widgetOptions);

        $(widgetOptionsRoot.widgetItem).each(function (index) {
            $(this).find('*').each(function () {
                // update "id" attribute
                _updateAttrID($(this), index);
                // update "name" attribute
                _updateAttrName($(this), index);
            });
        });
    };

    const _fixFormValidatonInput = function (widgetOptions, attribute, id, name) {
        if (attribute !== undefined) {
            attribute = $.extend(true, {}, attribute);
            attribute.id = id;
            attribute.container = ".field-" + id;
            attribute.input = "#" + id;
            attribute.name = name;
            attribute.value = $("#" + id).val();
            attribute.status = 0;

            let form = $("#" + widgetOptions.formId);
            if (form.yiiActiveForm("find", id) !== "undefined") {
                form.yiiActiveForm("remove", id);
            }
            form.yiiActiveForm("add", attribute);
        }
    };

    const _fixFormValidaton = function (widgetOptions) {
        const widgetOptionsRoot = _getWidgetOptionsRoot(widgetOptions);

        $(widgetOptionsRoot.widgetBody).find('input, textarea, select').each(function () {
            const id = $(this).attr('id');
            const name = $(this).attr('name');

            if (id !== undefined && name !== undefined) {
                const currentWidgetOptions = eval($(this).closest('div[data-dynamicform]').attr('data-dynamicform'));
                let matches = id.match(regexID);

                if (matches && matches.length === 4) {
                    matches[2] = matches[2].substring(1, matches[2].length - 1);
                    const level = _getLevel($(this));
                    const identifiers = _createIdentifiers(level - 1);
                    const baseID = matches[1] + '-' + identifiers.join('-') + '-' + matches[3];
                    const attribute = $("#" + currentWidgetOptions.formId).yiiActiveForm("find", baseID);
                    _fixFormValidatonInput(currentWidgetOptions, attribute, id, name);
                }
            }
        });
    };

    const _restoreKrajeeDepdrop = function ($elem) {
        const configDepdrop = $.extend(true, {}, eval($elem.attr('data-krajee-depdrop')));
        const inputID = $elem.attr('id');
        const matchID = inputID.match(regexID);

        if (matchID && matchID.length === 4) {
            for (let index = 0; index < configDepdrop.depends.length; ++index) {
                const match = configDepdrop.depends[index].match(regexID);
                if (match && match.length === 4) {
                    configDepdrop.depends[index] = match[1] + matchID[2] + match[3];
                }
            }
        }

        $elem.depdrop(configDepdrop);
    };

    const _restoreSpecialJs = function (widgetOptions) {
        const widgetOptionsRoot = _getWidgetOptionsRoot(widgetOptions);

        // "jquery.inputmask"
        let $hasInputmask = $(widgetOptionsRoot.widgetItem).find('[data-plugin-inputmask]');
        if ($hasInputmask.length > 0) {
            $hasInputmask.each(function () {
                $(this).inputmask('remove');
                $(this).inputmask(eval($(this).attr('data-plugin-inputmask')));
            });
        }

        // "kartik-v/yii2-widget-datepicker"
        let $hasDatepicker = $(widgetOptionsRoot.widgetItem).find('[data-krajee-datepicker]');
        if ($hasDatepicker.length > 0) {
            $hasDatepicker.each(function () {
                $(this).parent().removeData().datepicker('remove');
                $(this).parent().datepicker(eval($(this).attr('data-krajee-datepicker')));
            });
        }

        // "kartik-v/yii2-widget-timepicker"
        let $hasTimepicker = $(widgetOptionsRoot.widgetItem).find('[data-krajee-timepicker]');
        if ($hasTimepicker.length > 0) {
            $hasTimepicker.each(function () {
                $(this).removeData().off();
                $(this).parent().find('.bootstrap-timepicker-widget').remove();
                $(this).unbind();
                $(this).timepicker(eval($(this).attr('data-krajee-timepicker')));
            });
        }

        // "kartik-v/yii2-money"
        let $hasMaskmoney = $(widgetOptionsRoot.widgetItem).find('[data-krajee-maskMoney]');
        if ($hasMaskmoney.length > 0) {
            $hasMaskmoney.each(function () {
                $(this).parent().find('input').removeData().off();
                const id = '#' + $(this).attr('id');
                const displayID = id + '-disp';
                $(displayID).maskMoney('destroy');
                $(displayID).maskMoney(eval($(this).attr('data-krajee-maskMoney')));
                $(displayID).maskMoney('mask', parseFloat($(id).val()));
                $(displayID).on('change', function () {
                    const numDecimal = $(displayID).maskMoney('unmasked')[0];
                    $(id).val(numDecimal);
                    $(id).trigger('change');
                });
            });
        }

        // "kartik-v/yii2-widget-fileinput"
        let $hasFileinput = $(widgetOptionsRoot.widgetItem).find('[data-krajee-fileinput]');
        if ($hasFileinput.length > 0) {
            $hasFileinput.each(function () {
                $(this).fileinput(eval($(this).attr('data-krajee-fileinput')));
            });
        }

        // "kartik-v/yii2-widget-touchspin"
        let $hasTouchSpin = $(widgetOptionsRoot.widgetItem).find('[data-krajee-TouchSpin]');
        if ($hasTouchSpin.length > 0) {
            $hasTouchSpin.each(function () {
                $(this).TouchSpin('destroy');
                $(this).TouchSpin(eval($(this).attr('data-krajee-TouchSpin')));
            });
        }

        // "kartik-v/yii2-widget-colorinput"
        let $hasSpectrum = $(widgetOptionsRoot.widgetItem).find('[data-krajee-spectrum]');
        if ($hasSpectrum.length > 0) {
            $hasSpectrum.each(function () {
                const id = '#' + $(this).attr('id');
                const sourceID = id + '-source';
                $(sourceID).spectrum('destroy');
                $(sourceID).unbind();
                $(id).unbind();
                let configSpectrum = eval($(this).attr('data-krajee-spectrum'));
                configSpectrum.change = function (color) {
                    jQuery(id).val(color.toString());
                };
                $(sourceID).attr('name', $(sourceID).attr('id'));
                $(sourceID).spectrum(configSpectrum);
                $(sourceID).spectrum('set', jQuery(id).val());
                $(id).on('change', function () {
                    $(sourceID).spectrum('set', jQuery(id).val());
                });
            });
        }

        // "kartik-v/yii2-widget-depdrop"
        let $hasDepdrop = $(widgetOptionsRoot.widgetItem).find('[data-krajee-depdrop]');
        if ($hasDepdrop.length > 0) {
            $hasDepdrop.each(function () {
                if ($(this).data('select2') === undefined) {
                    $(this).removeData().off();
                    $(this).unbind();
                    _restoreKrajeeDepdrop($(this));
                }
            });
        }

        // "kartik-v/yii2-widget-select2"
        let $hasSelect2 = $(widgetOptionsRoot.widgetItem).find('[data-krajee-select2]');
        if ($hasSelect2.length > 0) {
            $hasSelect2.each(function () {
                const id = $(this).attr('id');
                const configSelect2 = eval($(this).attr('data-krajee-select2'));

                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }

                let configDepdrop = $(this).data('depdrop');
                if (configDepdrop) {
                    configDepdrop = $.extend(true, {}, configDepdrop);
                    $(this).removeData().off();
                    $(this).unbind();
                    _restoreKrajeeDepdrop($(this));
                }
                let tgt = $('#' + id);
                $.when(tgt.select2(configSelect2)).done(initSelect2Loading(id, '.select2-container--krajee'));

                const kvClose = 'kv_close_' + id.replace(/-/g, '_');

                tgt.on('select2:opening', function (ev) {
                    initSelect2DropStyle(id, kvClose, ev);
                });

                tgt.on('select2:unselect', function () {
                    window[kvClose] = true;
                });

                if (configDepdrop) {
                    const loadingText = (configDepdrop.loadingText) ? configDepdrop.loadingText : 'Loading ...';
                    initDepdropS2(id, loadingText);
                }
            });
        }
    };

})(window.jQuery);