<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="./css/style.css">
    <title>Tricount</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js" integrity="sha512-pumBsjNRGGqkPzKHndZMaAG+bir374sORyzM3uulLV14lN5LyykqNk8eEeUlUkB3U0M4FApyaHraT65ihJhDpQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $(function() {
            $("#select-div").show();
            $("#sort-select").bind("change", (e) => {
                sortOperations(e);
            });
        });

        function sortOperations(e) {
            const options = e.target.children;

            for(const option of options) {
                if(option.selected) {
                    switch (option.value) {
                        case "date-asc":
                            sortByDateAsc();
                            break;
                    
                        case "date-desc":
                            sortByDateDesc();
                            break;
                    
                        case "amount-asc":
                            sortByAmountAsc();
                            break;
                    
                        case "amount-desc":
                            sortByAmountDesc();
                            break;
                    
                        case "initiator-asc":
                            sortByInitiatorAsc();
                            break;
                    
                        case "initiator-desc":
                            sortByInitiatorDesc();
                            break;
                    
                        case "title-asc":
                            sortByTitleAsc();
                            break;
                    
                        case "title-desc":
                            sortByTitleDesc();
                            break;
                    
                        default:
                            break;
                    }
                }
            }
        }

        function sortByDateAsc() {
            let list = $("#operation-list");
            let operations = $(".operations");

            operations.sort((a, b) => {
                if(new Date(a.childNodes[1].childNodes[3].childNodes[3].textContent).getTime() < new Date(b.childNodes[1].childNodes[3].childNodes[3].textContent).getTime()) {
                    return -1;
                }
            });

            list.html("");
            for(operation of operations) {
                list.append(operation);
            }
        }

        function sortByDateDesc() {
            let list = $("#operation-list");
            let operations = $(".operations");

            operations.sort((a, b) => {
                if(new Date(b.childNodes[1].childNodes[3].childNodes[3].textContent).getTime() < new Date(a.childNodes[1].childNodes[3].childNodes[3].textContent).getTime()) {
                    return -1;
                }
            });

            list.html("");
            for(operation of operations) {
                list.append(operation);
            }
        }

        function sortByAmountAsc() {
            let list = $("#operation-list");
            let operations = $(".operations");

            operations.sort((a, b) => {
                if(parseFloat(a.childNodes[1].childNodes[3].childNodes[1].textContent.replace("€", " ")) < parseFloat(b.childNodes[1].childNodes[3].childNodes[1].textContent.replace("€", " "))) {
                    return -1;
                }
            });

            list.html("");
            for(operation of operations) {
                list.append(operation);
            }
        }

        function sortByAmountDesc() {
            let list = $("#operation-list");
            let operations = $(".operations");

            operations.sort((a, b) => {
                if(parseFloat(b.childNodes[1].childNodes[3].childNodes[1].textContent.replace("€", " ")) < parseFloat(a.childNodes[1].childNodes[3].childNodes[1].textContent.replace("€", " "))) {
                    return -1;
                }
            });

            list.html("");
            for(operation of operations) {
                list.append(operation);
            }
        }

        function sortByTitleAsc() {
            let list = $("#operation-list");
            let operations = $(".operations");

            operations.sort((a, b) => {
                return a.childNodes[1].childNodes[1].childNodes[1].innerHTML.localeCompare(b.childNodes[1].childNodes[1].childNodes[1].innerHTML);
            });

            list.html("");
            for(operation of operations) {
                list.append(operation);
            }
        }

        function sortByTitleDesc() {
            let list = $("#operation-list");
            let operations = $(".operations");

            operations.sort((a, b) => {
                return b.childNodes[1].childNodes[1].childNodes[1].innerHTML.localeCompare(a.childNodes[1].childNodes[1].childNodes[1].innerHTML);
            });

            list.html("");
            for(operation of operations) {
                list.append(operation);
            }
        }

        function sortByInitiatorAsc() {
            let list = $("#operation-list");
            let operations = $(".operations");

            operations.sort((a, b) => {
                return a.childNodes[1].childNodes[1].childNodes[3].innerHTML.localeCompare(b.childNodes[1].childNodes[1].childNodes[3].innerHTML);
            });

            list.html("");
            for(operation of operations) {
                list.append(operation);
            }
        }

        function sortByInitiatorDesc() {
            let list = $("#operation-list");
            let operations = $(".operations");

            operations.sort((a, b) => {
                return b.childNodes[1].childNodes[1].childNodes[3].innerHTML.localeCompare(a.childNodes[1].childNodes[1].childNodes[3].innerHTML);
            });

            list.html("");
            for(operation of operations) {
                list.append(operation);
            }
        }
    </script>
</head>

<body>
    <h2 class="header">Tricount <?= $tricount->title ?>    |</h2>
    <h3 class="header">
        <a class="button hover" href="tricount/index">Back</a>
        <a class="button hover" href="tricount/edit_tricount/<?= $tricount->id ?>">Edit</a>
        <a class="button hover" href="tricount/index"><?= $user->full_name . " (" . $user->role . ")"?></a>
    </h3>
    <?php if(count($tricount->get_participants()) == 1 && count($operations) == 0) : ?>
        <?php if (count($success) != 0): ?>
            <div class="success">
                <ul>
                    <?php foreach ($success as $success_message): ?>
                        <li>
                            <?= $success_message; ?>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <h3>You are alone! Click below to add participants!</h3>
        <a class="button hover" href="tricount/add_participant/<?= $tricount->id ?>">Add Participant</a>
    <?php elseif(count($tricount->get_participants()) > 1 && count($operations) == 0) : ?>
        <?php if (count($success) != 0): ?>
            <div class="success">
                <ul>
                    <?php foreach ($success as $success_message): ?>
                        <li>
                            <?= $success_message; ?>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <h3>Tricount empty! Click below to add first operation!</h3>
        <a class="button hover" href="operation/add_operation/<?= $tricount->id ?>">Add Operation</a>
    <?php else : ?>
        <?php if (count($success) != 0): ?>
            <div class="success">
                <ul>
                    <?php foreach ($success as $success_message): ?>
                        <li>
                            <?= $success_message; ?>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="choices">
            <a class="button hover" href="tricount/balance/<?= $tricount->id ?>">View Balance</a>
            <a class="button hover" href="operation/add_operation/<?= $tricount->id ?>">Add Operation</a>
        </div>

        <div id="select-div" hidden>
            <label for="">Sort By</label>
            <select name="sort-select" id="sort-select">
                <option value="date-asc" selected>Date &#9650</option>
                <option value="date-desc">Date &#9660</option>
                <option value="amount-asc">Amount &#9650</option>
                <option value="amount-desc">Amount &#9660</option>
                <option value="initiator-asc">Initiator &#9650</option>
                <option value="initiator-desc">Initiator &#9660</option>
                <option value="title-asc">Title &#9650</option>
                <option value="title-desc">Title &#9660</option>
            </select>
        </div>

        <div id="operation-list" class="list">
            <?php foreach($operations as $operation) : ?>
                <a class="operations" href="operation/index/<?= $operation->id ?>">
                    <div class="item hover">
                        <div class="item-left">
                            <p class="operation-title"><?= $operation->title ?></p>
                            <p class="operation-paid-by">Paid by <?= $operation->get_initiator_full_name() ?></p>
                        </div>
                        <div class="item-right">
                            <p class="operation-amount"><?= round($operation->amount, 2) ?> €</p>
                            <p class="operation-date"><?= $operation->operation_date ?></p>
                        </div>
                    </div>
                </a>
            <?php endforeach ?>
        </div>
    <?php endif; ?>

    <div class="item totals">
        <div class="item-left">
            <p class="my-total">MY TOTAL</p>
            <p class="operation-amount"> <?= $my_total ?> €</p>
        </div>
        <div class="item-right">
            <p class="total">TOTAL</p>
            <p class="operation-amount"> <?= $total ?> €</p>
        </div>
    </div>

</body>
</html>