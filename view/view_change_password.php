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
    <script>
        let passwordSuccess;

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
            if (await getValue() === 1)
                justvalidate();
        });

        function justvalidate() {
            $("input:text:first").focus();

            const validation = new JustValidate('#form', {
                validateBeforeSubmitting: true,
                lockForm: true,
                focusInvalidField: false,
                successLabelCssClass: ['success'],
                errorLabelCssClass: ['errors']
            });

            validation
                .addField('#password', [{
                        rule: 'required',
                        errorMessage: 'Password is required'
                    },
                    {
                        validator: function(password) {
                            return function() {
                                return $.post("main/password_success_service/", {
                                    password: password
                                });
                            }
                        },
                        errorMessage: 'Incorrect password'
                    }
                ], {
                    successMessage: 'Looks good !'
                })

                .addField('#new-password', [{
                        rule: 'required',
                        errorMessage: 'new Password is required'
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

                .addField('#password-confirm', [{
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
                            if (fields['#new-password'] && fields['#new-password'].elem) {
                                const repeatPasswordValue = fields['#new-password'].elem.value;
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
                    let password = $("#password").val();
                    passwordSuccess = await $.post("main/password_success_service/", {
                        password: password
                    });
                    passwordSuccess = JSON.parse(passwordSuccess);
                    if (!passwordSuccess)
                        this.showErrors({
                            '#password': 'Incorrect password'
                        });
                }))

                .onSuccess(function(event) {
                    event.target.submit();
                });
        }
    </script>
</head>

<body>
    <h2 class="header">CHANGE PASSWORD |</h2>
    <h3 class="header">
        <a class="button hover" href="main/settings">Back</a>
        <a class="button hover" href="tricount/index"><?= $user->full_name . " (" . $user->role . ")" ?></a>
    </h3>

    <form id="form" class="form" action="main/change_password" method="post">
        <div>
            <label>Actual Password</label>
            <input id="password" class="input-in-div" type="password" name="actual_password" value="<?= $actual_password ?>">
            <p class="passwordError"></p>
            <?php if (count($actual_password_errors) != 0) : ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($actual_password_errors as $actual_password_error) : ?>
                            <li>
                                <?= $actual_password_error; ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <label>New Password</label>
            <input class="input-in-div" id="new-password" type="password" name="new_password" value="<?= $new_password ?>">
            <p class="newPasswordError"></p>
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
            <label>New Password Confirm</label>
            <input id="password-confirm" class="input-in-div" type="password" name="new_password_confirm" value="<?= $new_password_confirm ?>">
            <p class="passwordConfirmError"></p>
        </div>

        <button class="form-button" type="submit">Save</button>
    </form>
</body>

</html>