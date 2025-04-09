    <?php
session_start();


// Vérifier la session de l'utilisateur
if (!isset($_SESSION['username'])) {
    echo "Accès interdit.";
    header("Location: connexion.html");
    exit;
} else {
     echo "Bienvenue " . $_SESSION['username'];
}

// Récupérer ID utilisateur
$idUser = $_SESSION['idUser'];

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$myDB = "forum";
$conn = new mysqli($servername, $username, $password, $myDB);

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Ajouter un post à la DB
/* if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title_post = $_POST['title_post'];
    $title_category = $_POST['title_category'];
    $content_post = $_POST['content_post'];

    $sql = "INSERT INTO Post (idUser, idCategory, Title"
} */




// Demander à l'utilisateur d'ajouter une Catégorie
if (isset($_POST['submit_category'])) {
    $title_category_request = $_POST['title_category_request'];
    $description_category_request = $_POST['description_category_request'];
    $sql = "INSERT INTO Category (idUser, Title, Description) VALUES (?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt -> bind_param("iss", $idUser, $title_category_request, $description_category_request);

        if ($stmt->execute()) {
            echo "Demande créé, attendez avant ";
            header("Location: page_membre.php");
        } else {
            echo "Erreur lors de la demande:";
        }
    }
}

// Récupérer les catégories demandées de l'utilisateur
$sql = "SELECT Users.Username,
               Category.title, Category.description, Category.is_Approved 
        FROM Users
        LEFT JOIN Category ON Users.idUser = Category.IdUser
        WHERE Category.IdUser = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUser);
$stmt->execute();
$resultCategoryUserAsked = $stmt->get_result();

// Récupérer les catégories qui sont approvées
$sql = "SELECT idCategory, title FROM Category WHERE is_Approved IS NOT NULL";
$stmt = $conn->prepare($sql);
$stmt->execute();
$resultCategory = $stmt->get_result();

// Récupérer tous les posts
$sql = "SELECT Post.idPost, Post.Title, Users.Username FROM Post
        JOIN Users ON Post.idUser = Users.idUser";
$stmt = $conn->prepare($sql);
$stmt->execute();
$resultPosts = $stmt->get_result();

// Créer un post 
if (isset($_POST['submit_post'])) {
    $title_post = $_POST['title_post'];
    $content_post = $_POST['Content_post'];
    $idCategory = $_POST['title_category'];  // Récupérer l'idCategory de la liste déroulante
    $idUser = $_SESSION['idUser'];  // Assurez-vous que l'idUser est récupéré depuis la session

    $sql = "INSERT INTO Post (idUser, Title, Content, idCategory) VALUES (?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("isss", $idUser, $title_post, $content_post, $idCategory);

        if ($stmt->execute()) {
            echo "Post créé ";
            header("Location: page_membre.php");
        } else {
            echo "Erreur lors de la demande : " . $stmt->error;
        }
    } else {
        echo "Erreur de préparation de la requête : " . $conn->error;
    }
}




?>



<!DOCTYPE HTML>
<html>
    
<head>
    <title>Forum - Page membre</title>
    <meta charset = "UTF-8">
    <meta name = "author" content = "moi">
    <meta name = "description" content = "forum">
    <link rel="stylesheet" href="page_membre.css">
</head>

<body>
    <h2>Créez un post</h2>

<form method="post" action="page_membre.php">

    <label for="title_post">Titre du post :</label>
    <input type="text" name="title_post" id="title_post" required>

    <label for="category_post">Catégorie du post :</label>
    <select name="title_category" id="category_post" required>
        <option value="" disabled selected>Choisissez une catégorie</option>
        <?php while ($row = $resultCategory->fetch_assoc()): ?>
            <option value="<?= $row['idCategory'] ?>"><?= htmlspecialchars($row['title']) ?></option>
        <?php endwhile; ?>
    </select>

    <label for="content_post">Contenu du post :</label>
    <input type="text" name="Content_post" id="content_post" required>

    <!-- Boutons -->
    <p><input type="submit" name="submit_post" value="OK"></p>
    <input type="reset" value="Réinitialiser"><br><br>

</form>




    <h2>Demandez l'ajout d'une catégorie</h2>

    <form method = "post" action=page_membre.php>


    <label for = "Nom de la catégorie">Nom de la catégorie:</label>
    <input type = "text" name = "title_category_request" id = "title_category_request">

    <label for = "Description de la catégorie">Description de la catégorie:</label>
    <input type = "text" name = "description_category_request" id = "description_category_request">


    <!-- Boutons -->
    <p><input type="submit" name = "submit_category" value="OK"></p>

    <!-- Reinitialiser-->
    <input type="reset" value="Réinitialiser"><br><br>

</form>


    <!-- Affichage catégories demandées -->
    <?php if ($resultCategoryUserAsked->num_rows > 0): ?>
    <h2>Vos catégories demandées :</h2>
    <ul>
        <?php while ($row = $resultCategoryUserAsked->fetch_assoc()): ?>
            <li>
                Titre : 
                <strong><?= htmlspecialchars($row['title']) ?></strong> 
                - Description : 
                <?= htmlspecialchars($row['description']) ?>

                (Statut : <?= $row['is_Approved'] ? "Approuvée" : "En attente" ?>)
            </li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>Aucune catégorie demandée</p>
<?php endif; ?>

    <h2>Tous les posts :</h2>

    <?php if ($resultPosts->num_rows > 0): ?>
        <?php while ($row = $resultPosts->fetch_assoc()): ?>
            <p class="paragraphe-posts">
                <a href="post_details.php?idPost=<?= $row['idPost'] ?>">
                    Titre : <strong><?= htmlspecialchars($row['Title']) ?></strong> - Auteur : <?= htmlspecialchars($row['Username']) ?>
                </a>
            </p>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Aucun post trouvé</p>
    <?php endif; ?>






</body>



</html>