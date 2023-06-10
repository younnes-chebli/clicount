<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="./css/style.css">
    <title>Add Tricount</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js" integrity="sha512-pumBsjNRGGqkPzKHndZMaAG+bir374sORyzM3uulLV14lN5LyykqNk8eEeUlUkB3U0M4FApyaHraT65ihJhDpQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js"></script>
    <script>
        let title_ok = true;
        let titleAvailable;
        const creator = <?= $user->id ?>;
        let justvalidate_config = false;

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

            /// validation formulaire | justvalidate

            if (await getValue() === 1) {
                justvalidate_config = true;
                justvalidate();
            } else {
                justvalidate_config = false;
                $("#title").bind("input", checkTitle);
                $("#title").bind("blur", checkTitleExists);
                $("#description").bind("input", checkDescription);
            }
        });

        /// justavalidate

        function justvalidate() {
            $("input:text:first").focus();

            const validation = new JustValidate('#add-tricount-form', {
                validateBeforeSubmitting: true,
                lockForm: true,
                focusInvalidField: false,
                successLabelCssClass: ['success'],
                errorLabelCssClass: ['errors']
            });

            validation
                .addField('#title', [{
                        rule: 'required',
                        errorMessage: 'Title is required'
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
                    },
                    {
                        validator: function(title) {
                            return function() {
                                return $.post("tricount/title_available_justvalidate_service/", {
                                    title: title,
                                    creator: creator
                                });
                            }
                        },
                        errorMessage: 'Title already exists for this creator'
                    }
                ], {
                    successMessage: 'Looks good !'
                })

                .addField('#description', [{
                        rule: 'minLength',
                        value: 3,
                        errorMessage: 'Minimum 3 charachters'
                    },
                    {
                        rule: 'maxLength',
                        value: 1024,
                        errorMessage: 'Maximum 1024 charachters'
                    }
                ])

                .onValidate(debounce(async function(event) {
                    let title = $("#title").val();
                    titleAvailable = await $.post("tricount/title_available_justvalidate_service/", {
                        title: title,
                        creator: creator
                    });
                    titleAvailable = JSON.parse(titleAvailable);
                    if (!titleAvailable)
                        this.showErrors({
                            '#title': 'Title already exists for this creator'
                        });
                }))

                .onSuccess(function(event) {
                    if (titleAvailable)
                        event.target.submit();
                });

        }

        /// validation formulaire

        function checkTitle() {
            let ok = true;
            $("#titleError").html("");

            const title = $("#title").val().trim();
            if (title.length < 3 || title.length > 256) {
                $("#titleError").html("Title length must be between 3 and 256");
                ok = false;
            }

            return ok;
        }

        async function checkTitleExists() {
            const newTitle = $("#title").val().trim();
            $("#titleError").html("");

            try {
                const response = await $.post("tricount/title_available_justvalidate_service/", {
                    title: newTitle,
                    creator: creator
                });

                const data = JSON.parse(response);

                if (!data && newTitle !== old_title) {
                    $("#titleError").html("Title already exists for this creator");
                    title_ok = false;
                }
            } catch (error) {
                throw error;
            }
        }

        function checkDescription() {
            let ok = true;
            $("#descriptionError").html("");

            const description = $("#description").val().trim();
            if (description.length > 0) {
                if (description.length < 3 || description.length > 1024) {
                    $("#descriptionError").html("Description length must be between 3 and 1024");
                    ok = false;
                }
            }

            $('#submitButton').prop('disabled', !ok);

            return ok;
        }

        function checkAll() {
            if (!justvalidate_config) {
                let ok = checkTitle();
                ok = checkDescription() && ok;
                ok = title_ok && ok;
                return ok;
            }
        }
    </script>
</head>

<body>
    <h2 class="header">ADD TRICOUNT |</h2>
    <h3 class="header">
        <a class="button hover" href="tricount/index">Back</a>
        <a class="button hover" href="tricount/index"><?= $user->full_name . " (" . $user->role . ")" ?></a>
    </h3>

    <form id="add-tricount-form" onsubmit="return checkAll();" class="form" action="tricount/add_tricount" method="post">
        <div>
            <label>Title</label>
            <input class="input-in-div" type="text" id="title" name="title" value="<?= $title ?>">
            <p id="titleError" class="errors"></p>
            <?php if (count($title_errors) != 0) : ?>
                <div id="errorsPHP" class="errors">
                    <ul>
                        <?php foreach ($title_errors as $title_error) : ?>
                            <li>
                                <?= $title_error; ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>


        <div>
            <label>Description (optionnal)</label>
            <textarea class="input-in-div" id="description" name="description" cols="30" rows="10"><?= $description ?></textarea>
            <p id="descriptionError" class="errors"></p>
            <?php if (count($description_errors) != 0) : ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($description_errors as $description_error) : ?>
                            <li>
                                <?= $description_error; ?>
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