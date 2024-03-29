<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Mur</title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body>
    <?php
        include "header.php";
        ?>
        <div id="wrapper">
            <?php
            /**
             * Etape 1: Le mur concerne un utilisateur en particulier
             * La première étape est donc de trouver quel est l'id de l'utilisateur
             * Celui ci est indiqué en parametre GET de la page sous la forme user_id=...
             * Documentation : https://www.php.net/manual/fr/reserved.variables.get.php
             * ... mais en résumé c'est une manière de passer des informations à la page en ajoutant des choses dans l'url
             */
            $userId =intval($_GET['user_id']);
            ?>
            <?php
            /**
             * Etape 2: se connecter à la base de donnée
             */
            include "connexionbdd.php";
            ?>

            <aside> 
                <?php
                /**
                 * Etape 3: récupérer le nom de l'utilisateur
                 */                
                $laQuestionEnSql = "SELECT * FROM users WHERE id= '$userId' ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                $user = $lesInformations->fetch_assoc();

                
                //@todo: afficher le résultat de la ligne ci dessous, remplacer XXX par l'alias et effacer la ligne ci-dessous
                // echo "<pre>" . print_r($user, 1) . "</pre>";
                $enCoursDeTraitement = isset($_POST['user_id']);
                if ($enCoursDeTraitement) {
                    //Bouton "s'abonner" : construction de la requete
                 $followedUserId = $_GET['user_id'];
                 $followingUserId = $_SESSION['connected_id'];

                 $abonnementInstructionSql = "INSERT INTO followers  "
                         . "(id, followed_user_id, following_user_id) "
                         . "VALUES (NULL, "
                         . $followedUserId . ", "
                         . "'" . $followingUserId . "')"
                         ;
                
                 $ok = $mysqli->query($abonnementInstructionSql);
                         if ( ! $ok)
                         {
                             echo "Impossible de s'abonner: " . $mysqli->error;
                         } else
                         {
                             echo "Abonnement réussi !";
                         } 
                }

                
                 
                ?>
                <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez tous les message de l'utilisatrice : <?php echo $user['alias'] ?>
                        (n° <?php echo $userId ?>)
                    </p>
                    <form method="post" action="">
                <input type="hidden" name="user_id" value="<?php echo $userId ?>">
                <button type="submit">S'abonner à <?php echo $user['alias'] ?></button>
            </form>
                </section>
            </aside>
            <main>
                <?php
                /**
                 * Etape 3: récupérer tous les messages de l'utilisatrice
                 */
                $laQuestionEnSql = "
                SELECT 
                    posts.content, 
                    posts.created, 
                    users.alias as author_name, 
                    users.id as author_id,
                    COUNT(likes.id) as like_number, 
                    GROUP_CONCAT(DISTINCT tags.label) AS taglist,
                    GROUP_CONCAT(DISTINCT tags.id) AS tag_ids -- Ajout de l'ID du tag
                FROM 
                    posts
                JOIN 
                    users ON users.id=posts.user_id
                LEFT JOIN 
                    posts_tags ON posts.id = posts_tags.post_id  
                LEFT JOIN 
                    tags ON posts_tags.tag_id = tags.id 
                LEFT JOIN 
                    likes ON likes.post_id = posts.id 
                WHERE 
                    posts.user_id='$userId' 
                GROUP BY 
                    posts.id
                ORDER BY 
                    posts.created DESC  
            ";
            
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                }

                /**
                 * Etape 4: @todo Parcourir les messsages et remplir correctement le HTML avec les bonnes valeurs php
                 */
            
                
                


                while ($post = $lesInformations->fetch_assoc())
                
                {

                    // echo "<pre>" . print_r($post, 1) . "</pre>";
                    ?>                
                    <article>
                        <h3>
                            <time datetime=''><?php echo $post['created'] ?></time>
                        </h3>
                        <address><a href="wall.php?user_id=<?php echo $post['author_id']; ?>">par <?php echo $post['author_name'] ?></a></address>
                        <div>
                            <p><?php echo $post['content'] ?></p>
                        </div>
                                                                 
                        <footer>
                            <small>♥ <?php echo $post['like_number'] ?></small>
                            <a href="http://localhost/resoc_n1/tags.php?tag_id= <?php echo $post['tag_ids'] ?>">#<?php echo $post['taglist'] ?></a>
                           
                        </footer>
                    </article>
                <?php } ?>


            </main>
        </div>
    </body>
</html>
