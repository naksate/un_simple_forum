<?php

session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$myDB = "forum";
$conn = new mysqli($servername, $username, $password, $myDB);

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}


// Récupérer le Post
if (isset($_GET['idPost'])) {
    $idPost = $_GET['idPost'];

    $sql = "SELECT Post.Title, Post.Content, Users.Username FROM Post
            JOIN Users ON Post.idUser = Users.idUser
            WHERE Post.idPost = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $idPost);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $title = htmlspecialchars($row['Title']);
            $content = nl2br(htmlspecialchars($row['Content']));
            $author = htmlspecialchars($row['Username']);
        } else {
            echo "Post non trouvé.";
            exit;
        }
    } else {
        echo "Erreur lors de la récupération des détails du post.";
        exit;
    }
} else {
    echo "Aucun post sélectionné.";
    exit;
}


// Récupérer les commentaires
if (isset($_POST['submit_comment'])) {
    // Récupérer les valeurs du formulaire
    $title_comment = $_POST['title_comment'];
    $content_comment = $_POST['content_comment'];
    $idUser = $_SESSION['idUser'];  // Récupérer l'idUser de la session
    $idPost = $_GET['idPost'];      // Récupérer l'id du post de l'URL

    // Insérer le commentaire dans la base de données
    $sql = "INSERT INTO Comment_post (idUser, idPost, title, Content) VALUES (?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iiss", $idUser, $idPost, $title_comment, $content_comment);

        if ($stmt->execute()) {
            echo "Commentaire ajouté avec succès !";
            header("Location: post_details.php?idPost=$idPost"); // Rediriger vers la même page pour afficher le commentaire
            exit();
        } else {
            echo "Erreur lors de l'ajout du commentaire : " . $stmt->error;
        }
    } else {
        echo "Erreur lors de la préparation de la requête : " . $conn->error;
    }
}

// Récupérer les commentaires du post
$sql_comments = "SELECT Comment_post.Title, Comment_post.Content, Users.Username FROM Comment_post
                 JOIN Users ON Comment_post.idUser = Users.idUser
                 WHERE Comment_post.idPost = ?";
$stmt_comments = $conn->prepare($sql_comments);
$stmt_comments->bind_param("i", $idPost);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();

?>

<!-- Affichage des détails du post -->
<h2><?= $title ?></h2>
<p><strong>Auteur : </strong><?= $author ?></p>
<p><strong>Contenu :</strong><br><?= $content ?></p>

<!-- Formulaire pour ajouter un commentaire -->
<h3>Ajouter un commentaire :</h3>
<form method="post" action="post_details.php?idPost=<?= $idPost ?>">
    <label for="title_comment">Titre du commentaire :</label>
    <input type="text" name="title_comment" id="title_comment" required> 

    <label for="content_comment">Contenu du commentaire :</label>
    <textarea name="content_comment" id="content_comment" rows="4" required></textarea>

    <p><input type="submit" name="submit_comment" value="Ajouter le commentaire"></p>
</form>

<!-- Affichage des commentaires -->

<h4>Afficher les commentaires</h4>

<ul>
<?php if ($result_comments->num_rows > 0): ?>
    <?php while ($row = $result_comments->fetch_assoc()): ?> 
        <li>
            <p><strong>Auteur : </strong><?= htmlspecialchars($row['Username']) ?></p>
            <p><strong>Titre : </strong><?= htmlspecialchars($row['Title']) ?></p>
            <p><strong>Contenu : </strong><?= nl2br(htmlspecialchars($row['Content'])) ?></p>
        </li>
    <?php endwhile; ?>
</ul>

<?php else: ?>
    <p>Aucun commentaire pour ce post.</p>
<?php endif; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post</title>
    <link rel="stylesheet" href="post_details.css"> 
</head>

<body><a href="page_membre.php">Retour à la page d'accueil</a></body>
