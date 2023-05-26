<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#35A768">

    <title><?= lang('page_title') . ' ' . $company_name ?></title>

    <link rel="stylesheet" type="text/css" href="<?= asset_url('assets/ext/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= asset_url('assets/ext/jquery-ui/jquery-ui.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= asset_url('assets/ext/cookieconsent/cookieconsent.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= asset_url('assets/css/frontend-andersonco.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= asset_url('assets/css/general.css') ?>">

    <link rel="icon" type="image/x-icon" href="<?= asset_url('assets/img/favicon.ico') ?>">
    <link rel="icon" sizes="192x192" href="<?= asset_url('assets/img/logo.png') ?>">

    <script src="<?= asset_url('assets/ext/fontawesome/js/fontawesome.min.js') ?>"></script>
    <script src="<?= asset_url('assets/ext/fontawesome/js/solid.min.js') ?>"></script>
    <script src="<?= asset_url('assets/js/upload_documents.js') ?>"></script>

    <script type="text/javascript">
        var inactivityTime = function () {
            var time;
            window.onload = resetTimer;
            // DOM Events
            document.onmousemove = resetTimer;
            document.onmousedown = resetTimer; // touchscreen presses
            document.ontouchstart = resetTimer;
            document.onclick = resetTimer;  
            document.onkeydown = resetTimer;

            function resetForm() {
                alert("Resetting form due to inactivity");
                window.location.href = window.location.href;
                window.location.reload();
            }

            function resetTimer() {
                clearTimeout(time);
                time = setTimeout(resetForm, 600000);  // 10 minutes = 600,000 ms
            }
        };
   </script>

   <script type="text/javascript">
        var inmateFilter = function () {
          $('#inmate-filter').keyup(function () {
            let valthis = $(this).val().toLowerCase();

            $('#select-inmate>option').each(function () {
                let text = $(this).text().toLowerCase();
                if (text.indexOf(valthis) === 0) {
                    $(this).show(); $(this).prop('selected',true);
                }
                else {
                    $(this).hide();
                }
            });
          });
        };
   </script>

    <script type="text/javascript">
        var birthDateV1 = function () {
          $('#visitor-1-birth-date').focusout(function () {
            const dateregex = /(\d{1,2})\/(\d{1,2})\/(\d{4})/;
            let valthis = $(this).val();
            if (valthis != "") {
                const matches = valthis.match(dateregex);
                if ((matches == null) || (matches.length == 0)) {
                    alert("Please enter the bithdate in the format mm/dd/yyyy");
                    $(this).val("");
                } else {
                    // Check the given date vs the current date to determine visitor age
                    let today = new Date();
                    try {
                        let bDate = new Date(matches[3], matches[1] - 1, matches[2]);
                        var age = today.getFullYear() - bDate.getFullYear();
                        var m = today.getMonth() - bDate.getMonth();
                        if (m < 0 || (m === 0 && today.getDate() < bDate.getDate())) {
                            age--;
                        }
                        if (age < 0) {
                            alert("Please enter a valid bithdate in the format mm/dd/yyyy");
                            $(this).val("");
                        } else {
                            if (age == 16) {
                                $('#visitor-1-dl-box').show();
                                $('#visitor-1-dl-file-name').addClass("required");
                                $('#visitor-1-dl-number-box').hide();
                                $('#visitor-1-dl-number').removeClass("required");
                                $('#visitor-1-dl-state-box').hide();
                                $('#visitor-1-dl-state').removeClass("required");
                            } else if (age >= 17) {
                                $('#visitor-1-dl-box').show();
                                $('#visitor-1-dl-file-name').addClass("required");
                                $('#visitor-1-dl-number-box').show();
                                $('#visitor-1-dl-number').addClass("required");
                                $('#visitor-1-dl-state-box').show();
                                $('#visitor-1-dl-state').addClass("required");
                            } else {
                                $('#visitor-1-dl-box').hide();
                                $('#visitor-1-dl-file-name').removeClass("required");
                                $('#visitor-1-dl-number-box').hide();
                                $('#visitor-1-dl-number').removeClass("required");
                                $('#visitor-1-dl-state-box').hide();
                                $('#visitor-1-dl-state').removeClass("required");
                            }
                        }
                    } catch (err) {
                        alert("Please enter a valid bithdate in the format mm/dd/yyyy");
                        $(this).val("");
                    }
                }
            } else {
                alert("Birth Date is required");
            }
          });
        };

        var birthDateV2 = function () {
          $('#visitor-2-birth-date').focusout(function () {
            const dateregex = /(\d{1,2})\/(\d{1,2})\/(\d{4})/;
            let valthis = $(this).val();
            if (valthis != "") {
                const matches = valthis.match(dateregex);
                if ((matches == null) || (matches.length == 0)) {
                    alert("Please enter the bithdate in the format mm/dd/yyyy");
                    $(this).val("");
                } else {
                    // Check the given date vs the current date to determine visitor age
                    let today = new Date();
                    try {
                        let bDate = new Date(matches[3], matches[1] - 1, matches[2]);
                        var age = today.getFullYear() - bDate.getFullYear();
                        var m = today.getMonth() - bDate.getMonth();
                        if (m < 0 || (m === 0 && today.getDate() < bDate.getDate())) {
                            age--;
                        }
                        if (age < 0) {
                            alert("Please enter a valid bithdate in the format mm/dd/yyyy");
                            $(this).val("");
                        } else {
                            if (age == 16) {
                                $('#visitor-2-dl-box').show();
                                $('#visitor-2-dl-file-name').addClass("required");
                                $('#visitor-2-dl-number-box').hide();
                                $('#visitor-2-dl-number').removeClass("required");
                                $('#visitor-2-dl-state-box').hide();
                                $('#visitor-2-dl-state').removeClass("required");
                                // Make email and phone not required
                                $('#visitor-2-show-required-email').hide();
                                $('#visitor-2-show-required-phone').hide();
                                $('#visitor-2-email').removeClass("required");
                                $('#visitor-2-phone-number').removeClass("required");
                            } else if (age == 17) {
                                $('#visitor-2-dl-box').show();
                                $('#visitor-2-dl-file-name').addClass("required");
                                $('#visitor-2-dl-number-box').show();
                                $('#visitor-2-dl-number').addClass("required");
                                $('#visitor-2-dl-state-box').show();
                                $('#visitor-2-dl-state').addClass("required");
                                // Make email and phone not required
                                $('#visitor-2-show-required-email').hide();
                                $('#visitor-2-show-required-phone').hide();
                                $('#visitor-2-email').removeClass("required");
                                $('#visitor-2-phone-number').removeClass("required");
                            } else if (age >= 18) {
                                $('#visitor-2-dl-box').show();
                                $('#visitor-2-dl-file-name').addClass("required");
                                $('#visitor-2-dl-number-box').show();
                                $('#visitor-2-dl-number').addClass("required");
                                $('#visitor-2-dl-state-box').show();
                                $('#visitor-2-dl-state').addClass("required");
                                // Make email and phone required
                                $('#visitor-2-show-required-email').show();
                                $('#visitor-2-show-required-phone').show();
                                $('#visitor-2-email').addClass("required");
                                $('#visitor-2-phone-number').addClass("required");
                            } else {
                                $('#visitor-2-dl-box').hide();
                                $('#visitor-2-dl-file-name').removeClass("required");
                                $('#visitor-2-dl-number-box').hide();
                                $('#visitor-2-dl-number').removeClass("required");
                                $('#visitor-2-dl-state-box').hide();
                                $('#visitor-2-dl-state').removeClass("required");
                                // Make email and phone not required
                                $('#visitor-2-show-required-email').hide();
                                $('#visitor-2-show-required-phone').hide();
                                $('#visitor-2-email').removeClass("required");
                                $('#visitor-2-phone-number').removeClass("required");
                            }
                        }
                    } catch (err) {
                        alert("Please enter a valid bithdate in the format mm/dd/yyyy");
                        $(this).val("");
                    }
                }
            } else {
                alert("Birth Date is required");
            }
          });
        };

        var birthDateV3 = function () {
          $('#visitor-3-birth-date').focusout(function () {
            const dateregex = /(\d{1,2})\/(\d{1,2})\/(\d{4})/;
            let valthis = $(this).val();
            if (valthis != "") {
                const matches = valthis.match(dateregex);
                if ((matches == null) || (matches.length == 0)) {
                    alert("Please enter the bithdate in the format mm/dd/yyyy");
                    $(this).val("");
                } else {
                    // Check the given date vs the current date to determine visitor age
                    let today = new Date();
                    try {
                        let bDate = new Date(matches[3], matches[1] - 1, matches[2]);
                        var age = today.getFullYear() - bDate.getFullYear();
                        var m = today.getMonth() - bDate.getMonth();
                        if (m < 0 || (m === 0 && today.getDate() < bDate.getDate())) {
                            age--;
                        }
                        if (age < 0) {
                            alert("Please enter a valid bithdate in the format mm/dd/yyyy");
                            $(this).val("");
                        } else {
                            if (age == 16) {
                                $('#visitor-3-dl-box').show();
                                $('#visitor-3-dl-file-name').addClass("required");
                                $('#visitor-3-dl-number-box').hide();
                                $('#visitor-3-dl-number').removeClass("required");
                                $('#visitor-3-dl-state-box').hide();
                                $('#visitor-3-dl-state').removeClass("required");
                                // Make email and phone not required
                                $('#visitor-3-show-required-email').hide();
                                $('#visitor-3-show-required-phone').hide();
                                $('#visitor-3-email').removeClass("required");
                                $('#visitor-3-phone-number').removeClass("required");
                            } else if (age == 17) {
                                $('#visitor-3-dl-box').show();
                                $('#visitor-3-dl-file-name').addClass("required");
                                $('#visitor-3-dl-number-box').show();
                                $('#visitor-3-dl-number').addClass("required");
                                $('#visitor-3-dl-state-box').show();
                                $('#visitor-3-dl-state').addClass("required");
                                // Make email and phone not required
                                $('#visitor-3-show-required-email').hide();
                                $('#visitor-3-show-required-phone').hide();
                                $('#visitor-3-email').removeClass("required");
                                $('#visitor-3-phone-number').removeClass("required");
                            } else if (age >= 18) {
                                $('#visitor-3-dl-box').show();
                                $('#visitor-3-dl-file-name').addClass("required");
                                $('#visitor-3-dl-number-box').show();
                                $('#visitor-3-dl-number').addClass("required");
                                $('#visitor-3-dl-state-box').show();
                                $('#visitor-3-dl-state').addClass("required");
                                // Make email and phone required
                                $('#visitor-3-show-required-email').show();
                                $('#visitor-3-show-required-phone').show();
                                $('#visitor-3-email').addClass("required");
                                $('#visitor-3-phone-number').addClass("required");
                            } else {
                                $('#visitor-3-dl-box').hide();
                                $('#visitor-3-dl-file-name').removeClass("required");
                                $('#visitor-3-dl-number-box').hide();
                                $('#visitor-3-dl-number').removeClass("required");
                                $('#visitor-3-dl-state-box').hide();
                                $('#visitor-3-dl-state').removeClass("required");
                                // Make email and phone not required
                                $('#visitor-3-show-required-email').hide();
                                $('#visitor-3-show-required-phone').hide();
                                $('#visitor-3-email').removeClass("required");
                                $('#visitor-3-phone-number').removeClass("required");
                            }
                        }
                    } catch (err) {
                        alert("Please enter a valid bithdate in the format mm/dd/yyyy");
                        $(this).val("");
                    }
                }
            } else {
                alert("Birth Date is required");
            }
          });
        };

        var useSameAddressV2 = function () {
          $('#visitor-2-use-same-address').change(function () {
            let isChecked = $(this).prop('checked');
            // if checked, copy the values from vis 1 address into vis 2
            if (isChecked) {
                $('#visitor-2-address').val($('#address').val());
                $('#visitor-2-city').val($('#city').val());
                $('#visitor-2-state').val($('#visitor-1-state').find(":selected").val());
                $('#visitor-2-zip-code').val($('#zip-code').val());
            } else {  // if not checked, erase the values
                $('#visitor-2-address').val("");
                $('#visitor-2-city').val("");
                $('#visitor-2-state').val("");
                $('#visitor-2-zip-code').val("");
            }
          });
        };

        var useSameAddressV3 = function () {
          $('#visitor-3-use-same-address').change(function () {
            let isChecked = $(this).prop('checked');
            // if checked, copy the values from vis 1 address into vis 2
            if (isChecked) {
                $('#visitor-3-address').val($('#address').val());
                $('#visitor-3-city').val($('#city').val());
                $('#visitor-3-state').val($('#visitor-1-state').find(":selected").val());
                $('#visitor-3-zip-code').val($('#zip-code').val());
            } else {  // if not checked, erase the values
                $('#visitor-3-address').val("");
                $('#visitor-3-city').val("");
                $('#visitor-3-state').val("");
                $('#visitor-3-zip-code').val("");
            }
          });
        };

        var addVisitor2 = function () {
          $('#button-add-visitor-2').click(function () {
            $('#visitor-2-info').show();
            $('#button-add-visitor-2').hide();
            $('#button-remove-visitor-2').show();
            $('#visitor-2-first-name').addClass('required');
            $('#visitor-2-last-name').addClass('required');
            $('#visitor-2-address').addClass('required');
            $('#visitor-2-city').addClass('required');
            $('#visitor-2-state').addClass('required');
            $('#visitor-2-zip-code').addClass('required');
            $('#visitor-2-birth-date').addClass('required');
          });
        }

        var removeVisitor2 = function () {
          $('#button-remove-visitor-2').click(function () {
            $('#visitor-2-info').hide();
            $('#button-add-visitor-2').show();
            $('#button-remove-visitor-2').hide();
            $('#visitor-2-first-name').removeClass('required');
            $('#visitor-2-last-name').removeClass('required');
            $('#visitor-2-address').removeClass('required');
            $('#visitor-2-city').removeClass('required');
            $('#visitor-2-state').removeClass('required');
            $('#visitor-2-zip-code').removeClass('required');
            $('#visitor-2-birth-date').removeClass('required');
            $('#visitor-2-dl-box').hide();
            $('#visitor-2-dl-file-name').removeClass("required");
            $('#visitor-2-dl-number-box').hide();
            $('#visitor-2-dl-number').removeClass("required");
            $('#visitor-2-dl-state-box').hide();
            $('#visitor-2-dl-state').removeClass("required");
            $('#visitor-2-first-name').val("");
            $('#visitor-2-last-name').val("");
            $('#visitor-2-address').val("");
            $('#visitor-2-city').val("");
            $('#visitor-2-state').val("");
            $('#visitor-2-zip-code').val("");
            $('#visitor-2-birth-date').val("");
            $('#visitor-2-dl-file-name').val("");
            $('#visitor-2-dl-number').val("");
            $('#visitor-2-dl-state').val("");
            $('#visitor-2-show-required-email').hide();
            $('#visitor-2-show-required-phone').hide();
            $('#visitor-2-email').removeClass("required");
            $('#visitor-2-phone-number').removeClass("required");
            $('#visitor-2-email').val("");
            $('#visitor-2-phone-number').val("");

            $('#button-add-visitor-3').hide();
            $('#button-remove-visitor-3').hide();
          });
        }

        var addVisitor3 = function () {
          $('#button-add-visitor-3').click(function () {
            $('#button-remove-visitor-2').hide();
            $('#visitor-3-info').show();
            $('#button-add-visitor-3').hide();
            $('#button-remove-visitor-3').show();
            $('#visitor-3-first-name').addClass('required');
            $('#visitor-3-last-name').addClass('required');
            $('#visitor-3-address').addClass('required');
            $('#visitor-3-city').addClass('required');
            $('#visitor-3-state').addClass('required');
            $('#visitor-3-zip-code').addClass('required');
            $('#visitor-3-birth-date').addClass('required');
          });
        }

        var removeVisitor3 = function () {
          $('#button-remove-visitor-3').click(function () {
            $('#button-remove-visitor-2').show();
            $('#visitor-3-info').hide();
            $('#button-add-visitor-3').show();
            $('#button-remove-visitor-3').hide();
            $('#visitor-3-first-name').removeClass('required');
            $('#visitor-3-last-name').removeClass('required');
            $('#visitor-3-address').removeClass('required');
            $('#visitor-3-city').removeClass('required');
            $('#visitor-3-state').removeClass('required');
            $('#visitor-3-zip-code').removeClass('required');
            $('#visitor-3-birth-date').removeClass('required');
            $('#visitor-3-first-name').val("");
            $('#visitor-3-last-name').val("");
            $('#visitor-3-address').val("");
            $('#visitor-3-city').val("");
            $('#visitor-3-state').val("");
            $('#visitor-3-zip-code').val("");
            $('#visitor-3-birth-date').val("");
            $('#visitor-3-dl-box').hide();
            $('#visitor-3-dl-file-name').removeClass("required");
            $('#visitor-3-dl-number-box').hide();
            $('#visitor-3-dl-number').removeClass("required");
            $('#visitor-3-dl-state-box').hide();
            $('#visitor-3-dl-state').removeClass("required");
            $('#visitor-3-dl-file-name').val("");
            $('#visitor-3-dl-number').val("");
            $('#visitor-3-dl-state').val("");
            $('#visitor-3-show-required-email').hide();
            $('#visitor-3-show-required-phone').hide();
            $('#visitor-3-email').removeClass("required");
            $('#visitor-3-phone-number').removeClass("required");
            $('#visitor-3-email').val("");
            $('#visitor-3-phone-number').val("");
          });
        }
    </script>

    <style>
            #loading {
        position: fixed;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0.7;
        background-color: #fff;
        z-index: 99;
        }

        #loading-image {
        z-index: 100;
        }
   </style>
</head>

<body onload="inactivityTime();inmateFilter();birthDateV1();birthDateV2();birthDateV3();useSameAddressV2();useSameAddressV3();addVisitor2();removeVisitor2();addVisitor3();removeVisitor3();">

<div id="loading" style="display: none;">
  <img id="loading-image" src="<?= asset_url('assets/img/loading.gif') ?>" alt="Loading..." />
</div>
<div id="main" class="container">
    <div class="row wrapper">
        <div id="book-appointment-wizard" class="col-12 col-lg-10 col-xl-8">

            <!-- FRAME TOP BAR -->

            <div id="header">
                <span id="company-name">
                    <?= $company_name ?><br />
                    <span class="company-address">
                        1200 East Lacy St<br />
                        Palestine, TX 75801<br />
                        (903) 729-6069
                    </span>
                </span>

                <div id="steps">
                    <div id="step-1" class="book-step active-step"
                         data-tippy-content="<?= lang('service_and_provider') ?>">
                        <strong>1</strong>
                    </div>

                    <div id="step-2" class="book-step" data-toggle="tooltip"
                         data-tippy-content="<?= lang('appointment_date_and_time') ?>">
                        <strong>2</strong>
                    </div>
                    <div id="step-3" class="book-step" data-toggle="tooltip"
                         data-tippy-content="<?= lang('customer_information') ?>">
                        <strong>3</strong>
                    </div>
                    <div id="step-4" class="book-step" data-toggle="tooltip"
                         data-tippy-content="<?= lang('appointment_confirmation') ?>">
                        <strong>4</strong>
                    </div>
                </div>

                <span id="vis-link">
                    VisitationLink by <image src="/assets/img/TarmacTech-small.png" height="20px"/> TarmacTech
                </span>
            </div>

            <?php if ($manage_mode): ?>
                <div id="cancel-appointment-frame" class="row booking-header-bar">
                    <div class="col-12 col-md-10">
                        <small><?= lang('cancel_appointment_hint') ?></small>
                    </div>
                    <div class="col-12 col-md-2">
                        <form id="cancel-appointment-form" method="post"
                              action="<?= site_url('appointments/cancel/' . $appointment_data['hash']) ?>">

                            <input type="hidden" name="csrfToken" value="<?= $this->security->get_csrf_hash() ?>"/>

                            <textarea name="cancel_reason" style="display:none"></textarea>

                            <button id="cancel-appointment" class="btn btn-warning btn-sm">
                                <?= lang('cancel') ?>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="booking-header-bar row">
                    <div class="col-12 col-md-10">
                        <small><?= lang('delete_personal_information_hint') ?></small>
                    </div>
                    <div class="col-12 col-md-2">
                        <button id="delete-personal-information"
                                class="btn btn-danger btn-sm"><?= lang('delete') ?></button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($exceptions)): ?>
                <div style="margin: 10px">
                    <h4><?= lang('unexpected_issues') ?></h4>

                    <?php foreach ($exceptions as $exception): ?>
                        <?= exceptionToHtml($exception) ?>
                    <?php endforeach ?>
                </div>
            <?php endif ?>

            <!-- Add a form "reset" button -->
            <div id="form-wizard-reset">
                <div class="command-buttons">
                    <button type="button" id="button-reset" class="btn button-reset btn-outline-primary">
                        Clear Form / Reset
                    </button>
                </div>
            </div>

            <!-- SELECT SERVICE AND PROVIDER -->

            <div id="wizard-frame-1" class="wizard-frame">
                <div class="frame-container">
                    <h2 class="frame-title"><?= lang('service_and_provider') ?></h2>

                    <div class="row frame-content">
                        <div class="col">
                            <div class="form-group">
                                <label for="select-service">
                                    <strong><?= lang('service') ?></strong>
                                </label>


                                <select id="select-service" class="form-control">
                                    <?php
                                    // Group services by category, only if there is at least one service with a parent category.
                                    $has_category = FALSE;
                                    foreach ($available_services as $service)
                                    {
                                        if ($service['category_id'] != NULL)
                                        {
                                            $has_category = TRUE;
                                            break;
                                        }
                                    }

                                    if ($has_category)
                                    {
                                        $grouped_services = [];

                                        foreach ($available_services as $service)
                                        {
                                            if ($service['category_id'] != NULL)
                                            {
                                                if ( ! isset($grouped_services[$service['category_name']]))
                                                {
                                                    $grouped_services[$service['category_name']] = [];
                                                }

                                                $grouped_services[$service['category_name']][] = $service;
                                            }
                                        }

                                        // We need the uncategorized services at the end of the list so we will use
                                        // another iteration only for the uncategorized services.
                                        $grouped_services['uncategorized'] = [];
                                        foreach ($available_services as $service)
                                        {
                                            if ($service['category_id'] == NULL)
                                            {
                                                $grouped_services['uncategorized'][] = $service;
                                            }
                                        }

                                        foreach ($grouped_services as $key => $group)
                                        {
                                            $group_label = ($key != 'uncategorized')
                                                ? $group[0]['category_name'] : 'Uncategorized';

                                            if (count($group) > 0)
                                            {
                                                echo '<optgroup label="' . $group_label . '">';
                                                foreach ($group as $service)
                                                {
                                                    echo '<option value="' . $service['id'] . '">'
                                                        . $service['name'] . '</option>';
                                                }
                                                echo '</optgroup>';
                                            }
                                        }
                                    }
                                    else
                                    {
                                        foreach ($available_services as $service)
                                        {
                                            echo '<option value="' . $service['id'] . '">' . $service['name'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>


          				    <div class="form-group">
                                <label for="select-inmate">
                                    <strong><?= lang('inmates') ?></strong>
                                </label>
                                <br/><span style="padding:10px 10px 10px 10px;">Filter by last name: <input type="text" id="inmate-filter" size="10" /></span>
                                <select id="select-inmate" size="3" class="form-control">
                                <?php 
                                //  First create an array of lastname, first middle
                                $inmateLFM = array();
                                foreach ($available_inmates as $inmate)
                                {
                                    $fmlname = strtolower(trim($inmate['inmate_name']));
                                    $fmlname = preg_replace('/\s+/', ' ', $fmlname);
                                    $nameparts = explode(' ', $fmlname);
                                    $inmateLastFirst = $fmlname;
                                    if (count($nameparts) > 3) {
                                        $inmateLastFirst = ucfirst($nameparts[count($nameparts) - 1]) . ", ";
                                        for ($i = 0; $i < count($nameparts) - 1; $i++) {
                                            $inmateLastFirst .= ucfirst($nameparts[$i]) . " ";
                                        }
                                    } else if (count($nameparts) == 3) {
                                        $inmateLastFirst = ucfirst($nameparts[2]) . ", " . ucfirst($nameparts[0]) . " " . ucfirst($nameparts[1]);
                                    } else if (count($nameparts) == 2) {
                                        $inmateLastFirst = ucfirst($nameparts[1]) . ", " . ucfirst($nameparts[0]);
                                    }
                                    $obj = array('id' => $inmate['id'], 'name' => $inmateLastFirst);
                                    array_push($inmateLFM, $obj);
                                }

                                usort($inmateLFM, function($a, $b) {
                                    return strcmp($a['name'], $b['name']);
                                });

                                foreach ($inmateLFM as $inmate)
                                {
                                    echo '<option value="' . $inmate['id'] . '">' . $inmate['name'] . '</option>';
                                }                            
            					?>
            					</select>
                            </div>       
                                                 
                            <div class="form-group" style="display:none;">
                                <label for="select-provider">
                                    <strong><?= lang('provider') ?></strong>
                                </label>

                                <select id="select-provider" class="form-control"></select>
                            </div>


                            <div id="service-description"></div>
                        </div>
                    </div>
                </div>

                <div class="command-buttons">
                    <span>&nbsp;</span>

                    <button type="button" id="button-next-1" class="btn button-next btn-dark"
                            data-step_index="1">
                        <?= lang('next') ?>
                        <i class="fas fa-chevron-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- SELECT APPOINTMENT DATE -->

            <div id="wizard-frame-2" class="wizard-frame" style="display:none;">
                <div class="frame-container">

                    <h2 class="frame-title"><?= lang('appointment_date_and_time') ?></h2>

                    <div class="row frame-content">
                        <div class="col-12 col-md-6">
                            <div id="select-date"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div id="select-time">
                                <div class="form-group" style="display:none;">
                                    <label for="select-timezone"><?= lang('timezone') ?></label>
                                    <?= render_timezone_dropdown('id="select-timezone" class="form-control" value="UTC"'); ?>
                                </div>

                                <div id="available-hours"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="command-buttons">
                    <button type="button" id="button-back-2" class="btn button-back btn-outline-secondary"
                            data-step_index="2">
                        <i class="fas fa-chevron-left mr-2"></i>
                        <?= lang('back') ?>
                    </button>
                    <button type="button" id="button-next-2" class="btn button-next btn-dark"
                            data-step_index="2">
                        <?= lang('next') ?>
                        <i class="fas fa-chevron-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- ENTER VISITOR DATA -->

            <div id="wizard-frame-3" class="wizard-frame" style="display:none;">
                <div class="frame-container">

                    <h2 class="frame-title"><?= lang('customer_information') ?></h2>

                    <div class="vl_information">
                    1. The inmate designates five (5) individuals who are authorized to visit.  Minors fifteen (15) years or younger are not required to be listed.  All visitors sixteen (16) years or older must show some form of picture identification.  Minors under sixteen (16) years of age must be accompanied by an adult.
                    <br/><br/>
                    2. Only three (3) people from the inmate's list may be in the visitation room at any one time.
                    </div>

                    <div class="row frame-content">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="first-name" class="control-label">
                                    <?= lang('first_name') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="first-name" class="required form-control" maxlength="100"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="last-name" class="control-label">
                                    <?= lang('last_name') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="last-name" class="required form-control" maxlength="120"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="address" class="control-label">
                                    <?= lang('address') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="address" class="required form-control" maxlength="120"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="city" class="control-label">
                                    <?= lang('city') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="city" class="required form-control" maxlength="120"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group" id="visitor-1-state-box">
                                <label for="visitor-1-state" class="control-label">
                                    <?= lang('state') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                
                                <select id="visitor-1-state" class="form-control">
                                    <option value="">Select</option>
                                    <option value="Alabama">Alabama</option>
                                    <option value="Alaska">Alaska</option>
                                    <option value="Arizona">Arizona</option>
                                    <option value="Arkansas">Arkansas</option>
                                    <option value="California">California</option>
                                    <option value="Colorado">Colorado</option>
                                    <option value="Connecticut">Connecticut</option>
                                    <option value="Delaware">Delaware</option>
                                    <option value="Florida">Florida</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Hawaii">Hawaii</option>
                                    <option value="Idaho">Idaho</option>
                                    <option value="Illinois">Illinois</option>
                                    <option value="Indiana">Indiana</option>
                                    <option value="Iowa">Iowa</option>
                                    <option value="Kansas">Kansas</option>
                                    <option value="Kentucky">Kentucky</option>
                                    <option value="Louisiana">Louisiana</option>
                                    <option value="Maine">Maine</option>
                                    <option value="Maryland">Maryland</option>
                                    <option value="Massachusetts">Massachusetts</option>
                                    <option value="Michigan">Michigan</option>
                                    <option value="Minnesota">Minnesota</option>
                                    <option value="Mississippi">Mississippi</option>
                                    <option value="Missouri">Missouri</option>
                                    <option value="Montana">Montana</option>
                                    <option value="Nebraska">Nebraska</option>
                                    <option value="Nevada">Nevada</option>
                                    <option value="New Hampshire">New Hampshire</option>
                                    <option value="New Jersey">New Jersey</option>
                                    <option value="New Mexico">New Mexico</option>
                                    <option value="New York">New York</option>
                                    <option value="North Carolina">North Carolina</option>
                                    <option value="North Dakota">North Dakota</option>
                                    <option value="Ohio">Ohio</option>
                                    <option value="Oklahoma">Oklahoma</option>
                                    <option value="Oregon">Oregon</option>
                                    <option value="Pennsylvania">Pennsylvania</option>
                                    <option value="Rhode Island">Rhode Island</option>
                                    <option value="South Carolina">South Carolina</option>
                                    <option value="South Dakota">South Dakota</option>
                                    <option value="Tennessee">Tennessee</option>
                                    <option value="Texas">Texas</option>
                                    <option value="Utah">Utah</option>
                                    <option value="Vermont">Vermont</option>
                                    <option value="Virginia">Virginia</option>
                                    <option value="Washington">Washington</option>
                                    <option value="West Virginia">West Virginia</option>
                                    <option value="Wisconsin">Wisconsin</option>
                                    <option value="Wyoming">Wyoming</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="zip-code" class="control-label">
                                    <?= lang('zip_code') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="zip-code" class="required form-control" maxlength="120"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="email" class="control-label">
                                    <?= lang('email') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="email" class="required form-control" maxlength="120"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="phone-number" class="control-label">
                                    <?= lang('phone_number') ?>
                                    <?= $require_phone_number === '1' ? '<span class="text-danger">*</span>' : '' ?>
                                </label>
                                <input type="text" id="phone-number" maxlength="60"
                                       class="<?= $require_phone_number === '1' ? 'required' : '' ?> form-control"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <!-- Display birth date box - if age is certain values, require further information -->
                            <div class="form-group">
                                <label for="visitor-1-birth-date" class="control-label">
                                    <?= lang('birth_date') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-1-birth-date" maxlength="10" class="required form-control"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group" id="visitor-1-dl-box" style="display:none;">
                                <label for="visitor-1-dl" class="control-label">
                                    <?= lang('visitor_1_dl') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="file" id="visitor-1-dl" class="form-control" maxlength="120" onchange="uploadDocument('visitor-1-dl')"/>
                                <input type="hidden" id="visitor-1-dl-file-name" value="" />
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group" id="visitor-1-dl-number-box" style="display:none;">
                                <label for="visitor-1-dl-number" class="control-label">
                                    <?= lang('visitor_1_dl_number') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-1-dl-number" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group" id="visitor-1-dl-state-box" style="display:none;">
                                <label for="visitor-1-dl-state" class="control-label">
                                    <?= lang('visitor_1_dl_state') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                
                                <select id="visitor-1-dl-state" class="form-control">
                                            <option value="">Select</option>
                                            <option value="Alabama">Alabama</option>
                                            <option value="Alaska">Alaska</option>
                                            <option value="Arizona">Arizona</option>
                                            <option value="Arkansas">Arkansas</option>
                                            <option value="California">California</option>
                                            <option value="Colorado">Colorado</option>
                                            <option value="Connecticut">Connecticut</option>
                                            <option value="Delaware">Delaware</option>
                                            <option value="Florida">Florida</option>
                                            <option value="Georgia">Georgia</option>
                                            <option value="Hawaii">Hawaii</option>
                                            <option value="Idaho">Idaho</option>
                                            <option value="Illinois">Illinois</option>
                                            <option value="Indiana">Indiana</option>
                                            <option value="Iowa">Iowa</option>
                                            <option value="Kansas">Kansas</option>
                                            <option value="Kentucky">Kentucky</option>
                                            <option value="Louisiana">Louisiana</option>
                                            <option value="Maine">Maine</option>
                                            <option value="Maryland">Maryland</option>
                                            <option value="Massachusetts">Massachusetts</option>
                                            <option value="Michigan">Michigan</option>
                                            <option value="Minnesota">Minnesota</option>
                                            <option value="Mississippi">Mississippi</option>
                                            <option value="Missouri">Missouri</option>
                                            <option value="Montana">Montana</option>
                                            <option value="Nebraska">Nebraska</option>
                                            <option value="Nevada">Nevada</option>
                                            <option value="New Hampshire">New Hampshire</option>
                                            <option value="New Jersey">New Jersey</option>
                                            <option value="New Mexico">New Mexico</option>
                                            <option value="New York">New York</option>
                                            <option value="North Carolina">North Carolina</option>
                                            <option value="North Dakota">North Dakota</option>
                                            <option value="Ohio">Ohio</option>
                                            <option value="Oklahoma">Oklahoma</option>
                                            <option value="Oregon">Oregon</option>
                                            <option value="Pennsylvania">Pennsylvania</option>
                                            <option value="Rhode Island">Rhode Island</option>
                                            <option value="South Carolina">South Carolina</option>
                                            <option value="South Dakota">South Dakota</option>
                                            <option value="Tennessee">Tennessee</option>
                                            <option value="Texas">Texas</option>
                                            <option value="Utah">Utah</option>
                                            <option value="Vermont">Vermont</option>
                                            <option value="Virginia">Virginia</option>
                                            <option value="Washington">Washington</option>
                                            <option value="West Virginia">West Virginia</option>
                                            <option value="Wisconsin">Wisconsin</option>
                                            <option value="Wyoming">Wyoming</option>
                                        </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="notes" class="control-label">
                                    <?= lang('notes') ?>
                                </label>
                                <textarea id="notes" maxlength="500" class="form-control" rows="1"></textarea>
                            </div>                        
                        </div>
                        <div class="col-12 col-md-6">
                            <br/>
                        </div>
                    </div>

                    <div class="row frame-content">
                        <hr style="width:95%;" />
                        <div class="col-12 col-md-6">
                            <div class="command-buttons">
                                <button type="button" id="button-add-visitor-2" class="btn btn-dark">
                                    Add Another Visitor
                                </button>
                                <button type="button" id="button-remove-visitor-2" class="btn btn-outline-secondary" style="display:none;">
                                    Remove Visitor
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Visitor 2 -->
                    <div class="row frame-content" id="visitor-2-info" style="display:none;">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="visitor-2-first-name" class="control-label">
                                    <?= lang('visitor_2_first_name') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-2-first-name" class="form-control" maxlength="100"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="visitor-2-last-name" class="control-label">
                                    <?= lang('visitor_2_last_name') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-2-last-name" class="form-control" maxlength="100"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <span>Use same address as Visitor 1 &nbsp;&nbsp</span>
                                <input type="checkbox" id="visitor-2-use-same-address"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="visitor-2-address" class="control-label">
                                    Visitor 2 <?= lang('address') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-2-address" class="form-control" maxlength="120"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="visitor-2-city" class="control-label">
                                    Visitor 2 <?= lang('city') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-2-city" class="form-control" maxlength="120"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group" id="visitor-2-state-box">
                                <label for="visitor-2-state" class="control-label">
                                    Visitor 2 <?= lang('state') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                
                                <select id="visitor-2-state" class="form-control">
                                    <option value="">Select</option>
                                    <option value="Alabama">Alabama</option>
                                    <option value="Alaska">Alaska</option>
                                    <option value="Arizona">Arizona</option>
                                    <option value="Arkansas">Arkansas</option>
                                    <option value="California">California</option>
                                    <option value="Colorado">Colorado</option>
                                    <option value="Connecticut">Connecticut</option>
                                    <option value="Delaware">Delaware</option>
                                    <option value="Florida">Florida</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Hawaii">Hawaii</option>
                                    <option value="Idaho">Idaho</option>
                                    <option value="Illinois">Illinois</option>
                                    <option value="Indiana">Indiana</option>
                                    <option value="Iowa">Iowa</option>
                                    <option value="Kansas">Kansas</option>
                                    <option value="Kentucky">Kentucky</option>
                                    <option value="Louisiana">Louisiana</option>
                                    <option value="Maine">Maine</option>
                                    <option value="Maryland">Maryland</option>
                                    <option value="Massachusetts">Massachusetts</option>
                                    <option value="Michigan">Michigan</option>
                                    <option value="Minnesota">Minnesota</option>
                                    <option value="Mississippi">Mississippi</option>
                                    <option value="Missouri">Missouri</option>
                                    <option value="Montana">Montana</option>
                                    <option value="Nebraska">Nebraska</option>
                                    <option value="Nevada">Nevada</option>
                                    <option value="New Hampshire">New Hampshire</option>
                                    <option value="New Jersey">New Jersey</option>
                                    <option value="New Mexico">New Mexico</option>
                                    <option value="New York">New York</option>
                                    <option value="North Carolina">North Carolina</option>
                                    <option value="North Dakota">North Dakota</option>
                                    <option value="Ohio">Ohio</option>
                                    <option value="Oklahoma">Oklahoma</option>
                                    <option value="Oregon">Oregon</option>
                                    <option value="Pennsylvania">Pennsylvania</option>
                                    <option value="Rhode Island">Rhode Island</option>
                                    <option value="South Carolina">South Carolina</option>
                                    <option value="South Dakota">South Dakota</option>
                                    <option value="Tennessee">Tennessee</option>
                                    <option value="Texas">Texas</option>
                                    <option value="Utah">Utah</option>
                                    <option value="Vermont">Vermont</option>
                                    <option value="Virginia">Virginia</option>
                                    <option value="Washington">Washington</option>
                                    <option value="West Virginia">West Virginia</option>
                                    <option value="Wisconsin">Wisconsin</option>
                                    <option value="Wyoming">Wyoming</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="visitor-2-zip-code" class="control-label">
                                    Visitor 2 <?= lang('zip_code') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-2-zip-code" class="form-control" maxlength="120"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="visitor-2-email" class="control-label">
                                    Visitor 2 <?= lang('email') ?>
                                    <span id="visitor-2-show-required-email" class="text-danger" style="display:none;">*</span>
                                </label>
                                <input type="text" id="visitor-2-email" class="form-control" maxlength="120"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="visitor-2-phone-number" class="control-label">
                                    Visitor 2 <?= lang('phone_number') ?>
                                    <span id="visitor-2-show-required-phone" class="text-danger" style="display:none;">*</span>
                                </label>
                                <input type="text" id="visitor-2-phone-number" maxlength="60" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <!-- Display birth date box - if age is certain values, require further information -->
                            <div class="form-group">
                                <label for="visitor-2-birth-date" class="control-label">
                                    <?= lang('birth_date') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-2-birth-date" maxlength="10" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group" id="visitor-2-dl-box" style="display:none;">
                                <label for="visitor-2-dl" class="control-label">
                                    <?= lang('visitor_2_dl') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="file" id="visitor-2-dl" class="form-control" maxlength="120" onchange="uploadDocument('visitor-2-dl')"/>
                                <input type="hidden" id="visitor-2-dl-file-name" value="" />
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group" id="visitor-2-dl-number-box" style="display:none;">
                                <label for="visitor-2-dl-number" class="control-label">
                                    <?= lang('visitor_2_dl_number') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-2-dl-number" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group" id="visitor-2-dl-state-box" style="display:none;">
                                <label for="visitor-2-dl-state" class="control-label">
                                    <?= lang('visitor_2_dl_state') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                
                                <select id="visitor-2-dl-state" class="form-control">
                                            <option value="">Select</option>
                                            <option value="Alabama">Alabama</option>
                                            <option value="Alaska">Alaska</option>
                                            <option value="Arizona">Arizona</option>
                                            <option value="Arkansas">Arkansas</option>
                                            <option value="California">California</option>
                                            <option value="Colorado">Colorado</option>
                                            <option value="Connecticut">Connecticut</option>
                                            <option value="Delaware">Delaware</option>
                                            <option value="Florida">Florida</option>
                                            <option value="Georgia">Georgia</option>
                                            <option value="Hawaii">Hawaii</option>
                                            <option value="Idaho">Idaho</option>
                                            <option value="Illinois">Illinois</option>
                                            <option value="Indiana">Indiana</option>
                                            <option value="Iowa">Iowa</option>
                                            <option value="Kansas">Kansas</option>
                                            <option value="Kentucky">Kentucky</option>
                                            <option value="Louisiana">Louisiana</option>
                                            <option value="Maine">Maine</option>
                                            <option value="Maryland">Maryland</option>
                                            <option value="Massachusetts">Massachusetts</option>
                                            <option value="Michigan">Michigan</option>
                                            <option value="Minnesota">Minnesota</option>
                                            <option value="Mississippi">Mississippi</option>
                                            <option value="Missouri">Missouri</option>
                                            <option value="Montana">Montana</option>
                                            <option value="Nebraska">Nebraska</option>
                                            <option value="Nevada">Nevada</option>
                                            <option value="New Hampshire">New Hampshire</option>
                                            <option value="New Jersey">New Jersey</option>
                                            <option value="New Mexico">New Mexico</option>
                                            <option value="New York">New York</option>
                                            <option value="North Carolina">North Carolina</option>
                                            <option value="North Dakota">North Dakota</option>
                                            <option value="Ohio">Ohio</option>
                                            <option value="Oklahoma">Oklahoma</option>
                                            <option value="Oregon">Oregon</option>
                                            <option value="Pennsylvania">Pennsylvania</option>
                                            <option value="Rhode Island">Rhode Island</option>
                                            <option value="South Carolina">South Carolina</option>
                                            <option value="South Dakota">South Dakota</option>
                                            <option value="Tennessee">Tennessee</option>
                                            <option value="Texas">Texas</option>
                                            <option value="Utah">Utah</option>
                                            <option value="Vermont">Vermont</option>
                                            <option value="Virginia">Virginia</option>
                                            <option value="Washington">Washington</option>
                                            <option value="West Virginia">West Virginia</option>
                                            <option value="Wisconsin">Wisconsin</option>
                                            <option value="Wyoming">Wyoming</option>
                                        </select>
                            </div>
                        </div>
                        <hr style="width:95%;" />
                        <div class="col-12 col-md-6">
                            <div class="command-buttons">
                                <button type="button" id="button-add-visitor-3" class="btn btn-dark">
                                    Add Another Visitor
                                </button>
                                <button type="button" id="button-remove-visitor-3" class="btn btn-outline-secondary" style="display:none;">
                                    Remove Visitor
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Visitor 3 -->
                    <div class="row frame-content" id="visitor-3-info" style="display:none;">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="visitor-3-first-name" class="control-label">
                                    <?= lang('visitor_3_first_name') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-3-first-name" class="form-control" maxlength="100"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="visitor-3-last-name" class="control-label">
                                    <?= lang('visitor_3_last_name') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-3-last-name" class="form-control" maxlength="100"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <span>Use same address as Visitor 1 &nbsp;&nbsp</span>
                                <input type="checkbox" id="visitor-3-use-same-address"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="visitor-3-address" class="control-label">
                                    Visitor 3 <?= lang('address') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-3-address" class="form-control" maxlength="120"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="visitor-3-city" class="control-label">
                                    Visitor 3 <?= lang('city') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-3-city" class="form-control" maxlength="120"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group" id="visitor-3-state-box">
                                <label for="visitor-3-state" class="control-label">
                                    Visitor 3 <?= lang('state') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                
                                <select id="visitor-3-state" class="form-control">
                                    <option value="">Select</option>
                                    <option value="Alabama">Alabama</option>
                                    <option value="Alaska">Alaska</option>
                                    <option value="Arizona">Arizona</option>
                                    <option value="Arkansas">Arkansas</option>
                                    <option value="California">California</option>
                                    <option value="Colorado">Colorado</option>
                                    <option value="Connecticut">Connecticut</option>
                                    <option value="Delaware">Delaware</option>
                                    <option value="Florida">Florida</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Hawaii">Hawaii</option>
                                    <option value="Idaho">Idaho</option>
                                    <option value="Illinois">Illinois</option>
                                    <option value="Indiana">Indiana</option>
                                    <option value="Iowa">Iowa</option>
                                    <option value="Kansas">Kansas</option>
                                    <option value="Kentucky">Kentucky</option>
                                    <option value="Louisiana">Louisiana</option>
                                    <option value="Maine">Maine</option>
                                    <option value="Maryland">Maryland</option>
                                    <option value="Massachusetts">Massachusetts</option>
                                    <option value="Michigan">Michigan</option>
                                    <option value="Minnesota">Minnesota</option>
                                    <option value="Mississippi">Mississippi</option>
                                    <option value="Missouri">Missouri</option>
                                    <option value="Montana">Montana</option>
                                    <option value="Nebraska">Nebraska</option>
                                    <option value="Nevada">Nevada</option>
                                    <option value="New Hampshire">New Hampshire</option>
                                    <option value="New Jersey">New Jersey</option>
                                    <option value="New Mexico">New Mexico</option>
                                    <option value="New York">New York</option>
                                    <option value="North Carolina">North Carolina</option>
                                    <option value="North Dakota">North Dakota</option>
                                    <option value="Ohio">Ohio</option>
                                    <option value="Oklahoma">Oklahoma</option>
                                    <option value="Oregon">Oregon</option>
                                    <option value="Pennsylvania">Pennsylvania</option>
                                    <option value="Rhode Island">Rhode Island</option>
                                    <option value="South Carolina">South Carolina</option>
                                    <option value="South Dakota">South Dakota</option>
                                    <option value="Tennessee">Tennessee</option>
                                    <option value="Texas">Texas</option>
                                    <option value="Utah">Utah</option>
                                    <option value="Vermont">Vermont</option>
                                    <option value="Virginia">Virginia</option>
                                    <option value="Washington">Washington</option>
                                    <option value="West Virginia">West Virginia</option>
                                    <option value="Wisconsin">Wisconsin</option>
                                    <option value="Wyoming">Wyoming</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="visitor-3-zip-code" class="control-label">
                                    Visitor 3 <?= lang('zip_code') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-3-zip-code" class="form-control" maxlength="120"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="visitor-3-email" class="control-label">
                                    Visitor 3 <?= lang('email') ?>
                                    <span id="visitor-3-show-required-email" class="text-danger" style="display:none;">*</span>
                                </label>
                                <input type="text" id="visitor-3-email" class="form-control" maxlength="120"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="visitor-3-phone-number" class="control-label">
                                    Visitor 3 <?= lang('phone_number') ?>
                                    <span id="visitor-3-show-required-phone" class="text-danger" style="display:none;">*</span>
                                </label>
                                <input type="text" id="visitor-3-phone-number" maxlength="60" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <!-- Display birth date box - if age is certain values, require further information -->
                            <div class="form-group">
                                <label for="visitor-3-birth-date" class="control-label">
                                    <?= lang('birth_date') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-3-birth-date" maxlength="10" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group" id="visitor-3-dl-box" style="display:none;">
                                <label for="visitor-3-dl" class="control-label">
                                    <?= lang('visitor_3_dl') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="file" id="visitor-3-dl" class="form-control" maxlength="120" onchange="uploadDocument('visitor-3-dl')"/>
                                <input type="hidden" id="visitor-3-dl-file-name" value="" />
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group" id="visitor-3-dl-number-box" style="display:none;">
                                <label for="visitor-3-dl-number" class="control-label">
                                    <?= lang('visitor_3_dl_number') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="visitor-3-dl-number" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group" id="visitor-3-dl-state-box" style="display:none;">
                                <label for="visitor-3-dl-state" class="control-label">
                                    <?= lang('visitor_3_dl_state') ?>
                                    <span class="text-danger">*</span>
                                </label>
                                
                                <select id="visitor-3-dl-state" class="form-control">
                                            <option value="">Select</option>
                                            <option value="Alabama">Alabama</option>
                                            <option value="Alaska">Alaska</option>
                                            <option value="Arizona">Arizona</option>
                                            <option value="Arkansas">Arkansas</option>
                                            <option value="California">California</option>
                                            <option value="Colorado">Colorado</option>
                                            <option value="Connecticut">Connecticut</option>
                                            <option value="Delaware">Delaware</option>
                                            <option value="Florida">Florida</option>
                                            <option value="Georgia">Georgia</option>
                                            <option value="Hawaii">Hawaii</option>
                                            <option value="Idaho">Idaho</option>
                                            <option value="Illinois">Illinois</option>
                                            <option value="Indiana">Indiana</option>
                                            <option value="Iowa">Iowa</option>
                                            <option value="Kansas">Kansas</option>
                                            <option value="Kentucky">Kentucky</option>
                                            <option value="Louisiana">Louisiana</option>
                                            <option value="Maine">Maine</option>
                                            <option value="Maryland">Maryland</option>
                                            <option value="Massachusetts">Massachusetts</option>
                                            <option value="Michigan">Michigan</option>
                                            <option value="Minnesota">Minnesota</option>
                                            <option value="Mississippi">Mississippi</option>
                                            <option value="Missouri">Missouri</option>
                                            <option value="Montana">Montana</option>
                                            <option value="Nebraska">Nebraska</option>
                                            <option value="Nevada">Nevada</option>
                                            <option value="New Hampshire">New Hampshire</option>
                                            <option value="New Jersey">New Jersey</option>
                                            <option value="New Mexico">New Mexico</option>
                                            <option value="New York">New York</option>
                                            <option value="North Carolina">North Carolina</option>
                                            <option value="North Dakota">North Dakota</option>
                                            <option value="Ohio">Ohio</option>
                                            <option value="Oklahoma">Oklahoma</option>
                                            <option value="Oregon">Oregon</option>
                                            <option value="Pennsylvania">Pennsylvania</option>
                                            <option value="Rhode Island">Rhode Island</option>
                                            <option value="South Carolina">South Carolina</option>
                                            <option value="South Dakota">South Dakota</option>
                                            <option value="Tennessee">Tennessee</option>
                                            <option value="Texas">Texas</option>
                                            <option value="Utah">Utah</option>
                                            <option value="Vermont">Vermont</option>
                                            <option value="Virginia">Virginia</option>
                                            <option value="Washington">Washington</option>
                                            <option value="West Virginia">West Virginia</option>
                                            <option value="Wisconsin">Wisconsin</option>
                                            <option value="Wyoming">Wyoming</option>
                                        </select>
                            </div>
                        </div>
                    </div>
                </div>
                   

                <?php if ($display_terms_and_conditions): ?>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="required form-check-input" id="accept-to-terms-and-conditions">
                        <label class="form-check-label" for="accept-to-terms-and-conditions">
                            <?= strtr(lang('read_and_agree_to_terms_and_conditions'),
                                [
                                    '{$link}' => '<a href="#" data-toggle="modal" data-target="#terms-and-conditions-modal">',
                                    '{/$link}' => '</a>'
                                ])
                            ?>
                        </label>
                    </div>
                <?php endif ?>

                <?php if ($display_privacy_policy): ?>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="required form-check-input" id="accept-to-privacy-policy">
                        <label class="form-check-label" for="accept-to-privacy-policy">
                            <?= strtr(lang('read_and_agree_to_privacy_policy'),
                                [
                                    '{$link}' => '<a href="#" data-toggle="modal" data-target="#privacy-policy-modal">',
                                    '{/$link}' => '</a>'
                                ])
                            ?>
                        </label>
                    </div>
                <?php endif ?>

                <div class="command-buttons">
                    <button type="button" id="button-back-3" class="btn button-back btn-outline-secondary"
                            data-step_index="3">
                        <i class="fas fa-chevron-left mr-2"></i>
                        <?= lang('back') ?>
                    </button>
                    <button type="button" id="button-next-3" class="btn button-next btn-dark"
                            data-step_index="3">
                        <?= lang('next') ?>
                        <i class="fas fa-chevron-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- APPOINTMENT DATA CONFIRMATION -->

            <div id="wizard-frame-4" class="wizard-frame" style="display:none;">
                <div class="frame-container">
                    <h2 class="frame-title"><?= lang('appointment_confirmation') ?></h2>
                    <div class="row frame-content">
                        <div id="appointment-details" class="col-12 col-md-6"></div>
                        <div id="customer-details" class="col-12 col-md-6"></div>
                        <span class="vl_information_red">
                        *REMINDER* BEFORE CONFIRMING YOUR VISIT, YOU MUST HAVE PROPER VALID GOVERNMENT IDENTIFICATION UPON ARRIVAL FOR YOUR VISIT.
                        </span>
                        <span class="visitor-restriction-message" style="display:none;">
                            NOTE: You cannot schedule an appointment with an inmate more than a week out from an existing appointment.
                            <br/><br/>
                            Please use the back buttons to pick a new date.
                        </span>
                    </div>
                    <?php if ($this->settings_model->get_setting('require_captcha') === '1'): ?>
                        <div class="row frame-content">
                            <div class="col-12 col-md-6">
                                <h4 class="captcha-title">
                                    CAPTCHA
                                    <button class="btn btn-link text-dark text-decoration-none py-0">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </h4>
                                <img class="captcha-image" src="<?= site_url('captcha') ?>">
                                <input class="captcha-text form-control" type="text" value=""/>
                                <span id="captcha-hint" class="help-block" style="opacity:0">&nbsp;</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="command-buttons">
                    <button type="button" id="button-back-4" class="btn button-back btn-outline-secondary"
                            data-step_index="4">
                        <i class="fas fa-chevron-left mr-2"></i>
                        <?= lang('back') ?>
                    </button>

                    <form enctype="multipart/form-data" id="book-appointment-form" style="display:inline-block" method="post">
                        <button id="book-appointment-submit" type="button" class="btn btn-success">
                            <i class="fas fa-check-square mr-2"></i>
                            <?= ! $manage_mode ? lang('confirm') : lang('update') ?>
                        </button>
                        <input type="hidden" name="csrfToken"/>
                        <input type="hidden" name="post_data"/>
                    </form>
                </div>
            </div>

            <!-- FRAME FOOTER -->

            <div id="frame-footer">
                <small>
                    <span class="footer-powered-by">
                        Provided by
                        <a href="https://tarmactech.com/home" target="_blank">Tarmac Technologies VisitationLink</a>
                        and Powered By
                        <a href="https://easyappointments.org" target="_blank">E!A</a>
                    </span>

                    <span class="footer-options">
                        <span id="select-language" class="badge badge-secondary">
                            <i class="fas fa-language mr-2"></i>
                            <?= ucfirst(config('language')) ?>
                        </span>

                        <a class="backend-link badge badge-primary" href="<?= site_url('backend'); ?>">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            <?= $this->session->user_id ? lang('backend_section') : lang('login') ?>
                        </a>
                    </span>
                </small>
            </div>
        </div>
    </div>
</div>

<?php if ($display_cookie_notice === '1'): ?>
    <?php require 'cookie_notice_modal.php' ?>
<?php endif ?>

<?php if ($display_terms_and_conditions === '1'): ?>
    <?php require 'terms_and_conditions_modal.php' ?>
<?php endif ?>

<?php if ($display_privacy_policy === '1'): ?>
    <?php require 'privacy_policy_modal.php' ?>
<?php endif ?>

<script>
    var GlobalVariables = {
        availableInmates: <?= json_encode($available_inmates) ?>,
        availableServices: <?= json_encode($available_services) ?>,
        availableProviders: <?= json_encode($available_providers) ?>,
        baseUrl: <?= json_encode(config('base_url')) ?>,
        manageMode: <?= $manage_mode ? 'true' : 'false' ?>,
        customerToken: <?= json_encode($customer_token) ?>,
        dateFormat: <?= json_encode($date_format) ?>,
        timeFormat: <?= json_encode($time_format) ?>,
        firstWeekday: <?= json_encode($first_weekday) ?>,
        displayCookieNotice: <?= json_encode($display_cookie_notice === '1') ?>,
        appointmentData: <?= json_encode($appointment_data) ?>,
        providerData: <?= json_encode($provider_data) ?>,
        customerData: <?= json_encode($customer_data) ?>,
        displayAnyProvider: <?= json_encode($display_any_provider) ?>,
        csrfToken: <?= json_encode($this->security->get_csrf_hash()) ?>
    };

    var EALang = <?= json_encode($this->lang->language) ?>;
    var availableLanguages = <?= json_encode(config('available_languages')) ?>;
</script>

<script src="<?= asset_url('assets/js/general_functions.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery/jquery.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery-ui/jquery-ui.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery-ui/jquery-ui-timepicker-addon.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/cookieconsent/cookieconsent.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/popper/popper.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/tippy/tippy-bundle.umd.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/datejs/date.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/moment/moment.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/moment/moment-timezone-with-data.min.js') ?>"></script>
<script src="<?= asset_url('assets/js/frontend_book_api.js') ?>"></script>
<script src="<?= asset_url('assets/js/frontend_book.js') ?>"></script>

<script>
    $(function () {
        FrontendBook.initialize(true, GlobalVariables.manageMode);
        GeneralFunctions.enableLanguageSelection($('#select-language'));
    });
</script>

<?php google_analytics_script(); ?>
</body>
</html>
