<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="./css/style.css">
    <title>Signup</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js" integrity="sha512-pumBsjNRGGqkPzKHndZMaAG+bir374sORyzM3uulLV14lN5LyykqNk8eEeUlUkB3U0M4FApyaHraT65ihJhDpQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js"></script>
    <script>
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
            if (await getValue() === 1) {
                $("input:text:first").focus();

                const validation = new JustValidate('#signupForm', {
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
                            validator: function(value) {
                                return function() {
                                    return $.post("main/mail_available_service/", {
                                        value: value
                                    });
                                }
                            },
                            errorMessage: 'Mail already exists',
                        },
                        // {
                        //     rule: 'customRegexp',
                        //     value: /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/,
                        //     errorMessage: 'Mail not valid'
                        // },
                        {
                            rule: 'email'
                        }
                    ], {
                        successMessage: 'Looks good !'
                    })

                    .addField('#full_name', [{
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

                    .addField('#password', [{
                            rule: 'required',
                            errorMessage: 'Password is required'
                        },
                        {
                            rule: 'minLength',
                            value: 8,
                            errorMessage: 'Minimum 8 charachters'
                        },
                        {
                            rule: 'maxLength',
                            value: 512,
                            errorMessage: 'Maximum 512 charachters'
                        },
                        {
                            rule: 'customRegexp',
                            value: /[A-Z]/,
                            errorMessage: 'Password must contain an uppercase letter'
                        },
                        {
                            rule: 'customRegexp',
                            value: /\d/,
                            errorMessage: 'Password must contain a digit'
                        },
                        {
                            rule: 'customRegexp',
                            value: /['";:,.\/?\\-]/,
                            errorMessage: 'Password must contain an special character'
                        }
                    ], {
                        successMessage: 'Looks good !'
                    })

                    .addField('#password_confirm', [{
                            rule: 'required',
                            errorMessage: 'Password confirm is required'
                        },
                        {
                            rule: 'minLength',
                            value: 8,
                            errorMessage: 'Minimum 8 charachters'
                        },
                        {
                            rule: 'maxLength',
                            value: 512,
                            errorMessage: 'Maximum 512 charachters'
                        },
                        {
                            rule: 'customRegexp',
                            value: /[A-Z]/,
                            errorMessage: 'Password must contain an uppercase letter'
                        },
                        {
                            rule: 'customRegexp',
                            value: /\d/,
                            errorMessage: 'Password must contain a digit'
                        },
                        {
                            rule: 'customRegexp',
                            value: /['";:,.\/?\\-]/,
                            errorMessage: 'Password must contain an special character'
                        },
                        {
                            validator: function(value, fields) {
                                if (fields['#password'] && fields['#password'].elem) {
                                    const repeatPasswordValue = fields['#password'].elem.value;
                                    return value === repeatPasswordValue;
                                }
                                return true;
                            },
                            errorMessage: 'Passwords should be the same',
                        }
                    ], {
                        successMessage: 'Looks good !'
                    })

                    .onValidate(debounce(async function(event) {
                        let value = $("#mail").val();
                        mailAvailable = await $.post("main/mail_available_service/", {
                            value: value
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
        });
    </script>
</head>

<body>
    <h2 class="header">TRICOUNT |</h2>
    <h3 class="header">
        <a class="button hover" href="main/index">Back</a>
    </h3>
    <h1>Signup</h1>
    <form id="signupForm" class="form" action="main/signup" method="post">
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
            <input class="input-in-div" id="full_name" type="text" name="full_name" value="<?= $full_name ?>">
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

        <div>
            <label>Password</label>
            <input class="input-in-div" id="password" type="password" name="password" value="<?= $password ?>">
            <p class="errors" id="passwordError"></p>
            <?php if (count($passwords_errors) != 0) : ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($passwords_errors as $password_error) : ?>
                            <li>
                                <?= $password_error; ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <label>Password Confirm</label>
            <input class="input-in-div" id="password_confirm" type="password" name="password_confirm" value="<?= $password_confirm ?>">
            <p class="errors" id="passwordConfirmError"></p>
        </div>

        <button class="form-button" type="submit">Signup</button>
    </form>
</body>

</html>