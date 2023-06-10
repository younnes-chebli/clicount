<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="./css/style.css">
    <title>Edit Profile</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js" integrity="sha512-pumBsjNRGGqkPzKHndZMaAG+bir374sORyzM3uulLV14lN5LyykqNk8eEeUlUkB3U0M4FApyaHraT65ihJhDpQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let edit = false;
        let mailAvailable;

        function debounce(fn, time) {
            var timer;

            return function() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    fn.apply(this, arguments);
                }, time);
            }
        }

        async function getValue() {
            return await $.getJSON("main/get_value_service");
        }

        $(async function() {
            ///justvalidate
            if (await getValue() === 1)
                justValidate();

            ///sweetalert
            $("#mail").bind("input", edited);
            $("#fullname").bind("input", edited);
            $("#iban").bind("input", edited);
            $("#back").click(function(event) {
                unsavedChanges(event);
            });
        });

        /// justvalidate

        function justValidate() {
            const validation = new JustValidate('#form', {
                validateBeforeSubmitting: true,
                lockForm: true,
                focusInvalidField: false,
                successLabelCssClass: ['success'],
                errorLabelCssClass: ['errors']
            });

            validation
                .addField('#mail', [{
                        rule: 'required',
                        errorMessage: 'Email is required'
                    },
                    {
                        validator: function(mail) {
                            return function() {
                                return $.post("main/mail_available_service_edit/", {
                                    mail: mail
                                });
                            }
                        },
                        errorMessage: 'Mail already exists',
                    },
                    {
                        rule: 'email'
                    }
                ], {
                    successMessage: 'Looks good !'
                })

                .addField('#fullname', [{
                        rule: 'required',
                        errorMessage: 'Fullname is required'
                    },
                    {
                        rule: 'minLength',
                        value: 3,
                        errorMessage: 'Minimum 3 charachters'
                    },
                    {
                        rule: 'maxLength',
                        value: 256,
                        errorMessage: 'Maximum 256 charachters'
                    }
                ], {
                    successMessage: 'Looks good !'
                })

                .addField('#iban', [{
                        rule: 'minLength',
                        value: 20,
                        errorMessage: 'Minimum 20 charachters'
                    },
                    {
                        rule: 'maxLength',
                        value: 256,
                        errorMessage: 'Maximum 256 charachters'
                    }
                ])

                .onValidate(debounce(async function(event) {
                    let mail = $("#mail").val();
                    mailAvailable = await $.post("main/mail_available_service_edit/", {
                        mail: mail
                    });
                    mailAvailable = JSON.parse(mailAvailable);
                    if (!mailAvailable)
                        this.showErrors({
                            '#mail': 'Mail already exists'
                        });
                }))

                .onSuccess(function(event) {
                    if (mailAvailable)
                        event.target.submit();
                });
        }

        /// sweetalert

        function edited() {
            edit = true;
        }

        function unsavedChanges(event) {
            if (edit) {
                event.preventDefault();

                Swal.fire({
                    title: 'Unsaved Changes',
                    html: `
                        <p>Are you sure you want to leave this form? Changes you made will not be saved.</p>
                    `,
                    icon: 'warning',
                    position: 'top',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Leave Page',
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed)
                        location.href = `main/settings`;
                });
            }
        }
    </script>
</head>

<body>
    <h2 class="header">EDIT PROFILE |</h2>
    <h3 class="header">
        <a id="back" class="button hover" href="main/settings">Back</a>
        <a class="button hover" href="tricount/index"><?= $user->full_name . " (" . $user->role . ")" ?></a>
    </h3>

    <form id="form" class="form" action="main/edit_profile" method="post">
        <div>
            <label>Mail</label>
            <input class="input-in-div" id="mail" type="text" name="mail" value="<?= $mail ?>">
            <p class="errors" id="mailError"></p>
            <?php if (count($mail_errors) != 0) : ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($mail_errors as $mail_error) : ?>
                            <li>
                                <?= $mail_error; ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <label>Fullname</label>
            <input class="input-in-div" id="fullname" type="text" name="full_name" value="<?= $full_name ?>">
            <p class="errors" id="fullnameError"></p>
            <?php if (count($full_name_errors) != 0) : ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($full_name_errors as $full_name_error) : ?>
                            <li>
                                <?= $full_name_error; ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <label>IBAN</label>
            <input class="input-in-div" id="iban" type="text" name="iban" value="<?= $iban ?>">
            <p class="errors" id="ibanError"></p>
            <?php if (count($iban_errors) != 0) : ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($iban_errors as $iban_error) : ?>
                            <li>
                                <?= $iban_error; ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <button class="form-button" type="submit">Save</button>
    </form>
</body>

</html>