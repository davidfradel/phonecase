<?php

require ('inc/bootstrap.php');

$auth = App::getAuth();
$db = App::getDatabase();
$auth->connectFromCookie($db);

$articles = json_decode(json_encode($auth ->getListArticle($db)), true);


require ('inc/header.phtml');
?>
<main>
    <h1>Une collection originale</h1>
    <section>
        <?php
        foreach ($articles as $iK => $aValues) {
                ?>
                <article>
                    <p>
                        <img src="<?= $aValues['image']; ?>"/><br/>
                    <p class="description"><?= $aValues['description']; ?><p>
                    <p>
                        <span><?= $aValues['prix']; ?> euros</span>
                    </p><br/>
                    <p>
                        <label for="q">Quantité: </label>
                        <select id="qt" name="q">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                        </select>
                    </p>
                    <p>
                        <?php
                        if ((isset($_SESSION['auth'])) && (!empty($_SESSION['auth']))) {
                            // le login a été enregistré dans la session, j'affiche le lien du profil
                            echo '<p class="add-to-cart" data-id="' . $aValues['id'] . '" data-name="' . $aValues['description'] . '" data-price="' . $aValues['prix'] . '">Ajouter au panier</p>';
                        } else {
                            // pas de login en session : proposer la connexion
                            echo '<p class="add-to-cart" onClick="document.location.href=\'login.phtml\'" data-id="' . $aValues['id'] . '" data-name="' . $aValues['description'] . '" data-price="' . $aValues['prix'] . '">Ajouter au panier</p>';
                        }
                        ?>
                    </p>
                    </p>
                </article>
                <?php
        }
        ?>
    </section>
    </main>
    <?php
    include ('inc/footer.phtml');