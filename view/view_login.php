<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="./css/style.css">
    <title>Login</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js" integrity="sha512-pumBsjNRGGqkPzKHndZMaAG+bir374sORyzM3uulLV14lN5LyykqNk8eEeUlUkB3U0M4FApyaHraT65ihJhDpQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js"></script>
    <script>
        let mailExist;

        $(function() {
            $("input:text:first").focus();

            const validation = new JustValidate('#loginForm', {
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
                                return $.post("main/mail_exists_service/", {
                                    value: value
                                });
                            }
                        },
                        errorMessage: 'Can t find a user with this mail. Please sign up.',
                    }
                ], {
                    successMessage: 'Looks good !'
                })

                .addField('#password', [{
                    rule: 'required',
                    errorMessage: 'Password is required'
                }])

                .onValidate(async function(event) {
                    let value = $("#mail").val();

                    mailExists = await $.post("main/mail_exists_service/", {
                        value: value
                    });
                    mailExists = JSON.parse(mailExists);

                    if (!mailExists && $('#mail').val() != '')
                        this.showErrors({
                            '#mail': 'Can t find a user with this mail. Please sign up.'
                        });

                })

                .onSuccess(function(event) {
                    if (mailExists && mailExists !== undefined)
                        event.target.submit();
                });
        });
    </script>
</head>

<body>
    <h2 class="header">TRICOUNT |</h2>
    <h3 class="header">
        <a class="button hover" href="main/index">Back</a>
    </h3>
    <h1>Login</h1>
    <div class="main">
        <form id="loginForm" action="main/login" method="post">
            <table>
                <tr>
                    <td>Email:</td>
                    <td><input id="mail" name="mail" type="text" value="<?= $mail ?>"></td>
                    <p class="errors" id="mailError"></p>
                </tr>
                <tr>
                    <td>Password:</td>
                    <td><input id="password" name="password" type="password" value="<?= $password ?>"></td>
                    <p class="errors" id="passwordError"></p>
                </tr>
            </table>
            <button type="submit">Login</button>
        </form>
        <?php if (count($errors) != 0) : ?>
            <div class='errors'>
                <p>Please correct the following error(s) :</p>
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>