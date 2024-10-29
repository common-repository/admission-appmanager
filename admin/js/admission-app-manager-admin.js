(function ($) {
    //'use strict';

    $(document).ready(function () {
        /*$('.aam_multiple').multipleSelect({
         filter: true
         });*/

        $('#_aam_combination_school').on('change', function () {
            get_school_programs($(this), '#_aam_combination_program');
        });

        $('#_aam_application_combination_school').on('change', function () {
            get_school_programs($(this), '#_aam_application_combination_program');
            get_school_intakes();
        });

        $('#_aam_application_combination_program').on('change', function () {
            get_school_intakes();
        });

        /**
         * Get programs for selected school
         *
         * @param el
         * @param program_dd_id
         */
        function get_school_programs(el, program_dd_id) {
            var school_id = el.val(),
                program = $(program_dd_id),
                reset = '<option>None</option>';

            console.log(school_id);

            if (school_id != '') {
                $.ajax({
                    url: ajaxurl,
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        action: 'get_programs',
                        school_id: school_id
                    },
                    success: function (data) {
                        program.empty().append(reset);

                        $.each(data, function (k, v) {
                            program.append('<option value="' + k + '">' + v + '</option>');
                        })
                    }
                })
            } else {
                program.empty().append(reset);
            }
        }

        function get_school_intakes() {
            var school_id = $('#_aam_application_combination_school').val(),
                program = $('#_aam_application_combination_program').val(),
                intakes = $('#_aam_application_combination_intake'),
                reset = '<option>None</option>';

            if (school_id != '' && program != '') {
                $.ajax({
                    url: ajaxurl,
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        action: 'get_intakes',
                        school_id: school_id,
                        program: program
                    },
                    success: function (data) {
                        intakes.empty().append(reset);

                        $.each(data, function (k, v) {
                            intakes.append('<option value="' + k + '">' + v + '</option>');
                        })
                    }
                })
            } else {
                intakes.empty().append(reset);
            }
        }

        $('#_aam_application_combination_school, #_aam_application_combination_program, #_aam_application_combination_intake').on('change', function () {
            var school = $('#_aam_application_combination_school'),
                program = $('#_aam_application_combination_program'),
                intake = $('#_aam_application_combination_intake'),
                combinations = $('#_aam_application_combination_combination'),
                round = $('#_aam_application_combination_round'),
                reset = '<option>None</option>';

            if (school.val() != '' && program.val() != '' && intake.val() != '') {
                $.ajax({
                    url: ajaxurl,
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        action: 'find_combination',
                        school: school.val(),
                        program: program.val(),
                        intake: intake.val()
                    },
                    success: function (data) {
                        if (data) {
                            combinations.val(data.id);

                            //console.log(data.rounds);

                            round.empty().append(reset);

                            for (var i = 1; i <= data.rounds; i++) {
                                round.append('<option value="' + i + '">' + i + '</option>');
                            }
                        } else {
                            round.empty().append(reset);
                            combinations.val('');
                        }
                    }
                });
            } else {
                round.empty().append(reset);
                combinations.val('');
            }
        });

        $('.progress-bar').each(function () {
            $(this).find('span').animate({
                width: $(this).attr('data-completion') + '%'
            }, 1000);
        });

        $('.aam-show-steps').on('click', 'a', function (e) {
            e.preventDefault();

            var link = $(this);
            var id = link.data('open');

            $('#' + id).toggle();
            link.toggleClass('closed');
        });

        $('.aam-toggle').on('click', function (e) {
            e.preventDefault();

            var link = $(this);
            var id = link.data('open');

            $('#' + id).toggle();
            link.toggleClass('closed');
        });

        /**
         * Set default document name on document type change
         */
        $('.aam-document-type').on('change', function() {
            var t = $(this).find('select').children('option:selected').text();
            $('.aam-document-name').find('input').val(t);
        });

    });

})(jQuery);
