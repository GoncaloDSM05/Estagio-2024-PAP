<?php
    include 'assets/php/conecta.php';
    session_start();

    if (!isset($_SESSION['idutilizador'])) {
        header("Location: conta.html");
        exit();
    }

    $idutilizador = $_SESSION['idutilizador'];

    $query = $mysqli->prepare("SELECT * FROM utilizadores WHERE idutilizador = ?");
    if ($query === false) {
        die("Erro preparando a query: " . $mysqli->error);
    }

    $query->bind_param("i", $idutilizador);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        die("Utilizador não encontrado.");
    }
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link rel="shortcut icon" type="imagex/png" href="images/logo.png">
    <link rel="stylesheet" href="assets/css/stylep.css">
    <title>Perfil - SquadForge</title>
</head>
<body>
    <div class="container">
        <div class="row gutters">
            <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="account-settings">
                            <div class="user-profile">
                                <div class="user-avatar">
                                    <img src="<?php echo htmlspecialchars($user['fotoPath']) ? 'assets/php/' . $user['fotoPath'] . '?' . time() : 'images/profile.png'; ?>" alt="Foto de Perfil">
                                </div>
                                <h5 class="user-name"><?php echo htmlspecialchars($user['primeironome']); ?> <?php echo htmlspecialchars($user['ultimonome']); ?></h5>
                                <h6 class="user-email"><?php echo htmlspecialchars($user['email']); ?></h6>
                            </div>
                            <div class="about">
                                <h5>Função</h5>
                                <p><?php echo htmlspecialchars($user['nomefuncao']); ?></p>
                            </div>
                        </div>
                        <br><br>
                        <div class="d-flex justify-content-center mt-4">
                            <button type="button" class="btn btn-secondary" onclick="window.location='dashboard.php';">Voltar</button>
                        </div>
                    </div>
                </div>
                </div>
                <div class="col-xl-9 col-lg-9 col-md-12 col-sm-12 col-12">
                    <div class="card h-100">
                        <div class="card-body">
                        <form action="assets/php/verificaPerfil.php" method="POST">
                            <input type="hidden" name="action" value="updateProfile">
                            <div class="row gutters">
                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                    <h6 class="mb-2" style="color: #5e3bee;"><strong>Detalhes Pessoais</strong></h6>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label for="firstName">Primeiro nome:</label>
                                        <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user['primeironome']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label for="lastName">Último nome:</label>
                                        <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($user['ultimonome']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label for="email">Email:</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label for="nomefuncao">Função:</label>
                                        <input type="text" class="form-control" id="nomefuncao" name="nomefuncao" value="<?php echo htmlspecialchars($user['nomefuncao']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label for="nomeutilizador">Nome de utilizador:</label>
                                        <input type="text" class="form-control" id="nomeutilizador" name="nomeutilizador" value="<?php echo htmlspecialchars($user['nomeutilizador']); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row gutters">
                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                    <h6 class="mt-3 mb-2" style="color: #5e3bee;"><strong>Outras informações</strong></h6>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label for="palavra-passe">Palavra-passe:</label>
                                        <input type="name" class="form-control" id="palavra-passe" placeholder="Clica aqui para alterar a palavra-passe">
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label for="fotoperfil">Foto de Perfil:</label>
                                        <input type="name" class="form-control" id="fotoperfil" placeholder="Clica aqui para alterar a foto de perfil">
                                    </div>
                                </div>
                            </div>
                            <div class="row gutters">
                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                    <div class="text-right">
                                        <button type="button" id="submit" name="submit" class="btn btn-danger" onclick="window.location='assets/php/logout.php';">Logout</button>
                                        <button type="submit" name="submit" class="btn" style="background-color: #5e3bee; color: #fff;" id="saveChangesButton" disabled>Salvar Alterações</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- Modal para Atualizar Palavra-passe -->
                    <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="passwordModalLabel">Atualizar Palavra-passe</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form action="assets/php/verificaPerfil.php" method="post">
                                        <input type="hidden" name="action" value="alterarPP">
                                        <div class="form-group">
                                            <label for="ppAtual">Palavra-Passe Atual:</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="ppAtual" name="ppAtual" required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">
                                                        <i class="fa fa-eye" onclick="togglePassword('ppAtual', this)"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="novaPP">Nova Palavra-Passe:</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="novaPP" name="novaPP" required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">
                                                        <i class="fa fa-eye" onclick="togglePassword('novaPP', this)"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="confirmNewPP">Confirmar Nova Palavra-Passe:</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="confirmNewPP" name="confirmNewPP" required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">
                                                        <i class="fa fa-eye" onclick="togglePassword('confirmNewPP', this)"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn" style="background-color: #5e3bee; color: #fff;">Salvar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Modal para Alterar Foto de Perfil -->
                    <div class="modal fade" id="profilePicModal" tabindex="-1" aria-labelledby="profilePicModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="profilePicModalLabel">Alterar Foto de Perfil</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form action="assets/php/verificaPerfilFoto.php" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="alterarFoto">
                                        <div class="form-group">
                                            <label for="profile-pic">Upload de Foto:</label>
                                            <input type="file" class="form-control-file" id="profile-pic" name="foto" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn" style="background-color: #5e3bee; color: #fff;">Salvar Alterações</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- Incluir jQuery primeiro -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>

    <!-- Popper.js para Bootstrap 4 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>


    <script>
        function togglePassword(inputId, icon) {
            var input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>


    <script>
        document.getElementById('palavra-passe').addEventListener('click', function() {
        $('#passwordModal').modal('show');
        });

        document.getElementById('fotoperfil').addEventListener('click', function() {
        $('#profilePicModal').modal('show');
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const saveChangesButton = document.getElementById('saveChangesButton');
            const inputs = document.querySelectorAll('#firstName, #lastName, #email, #nomefuncao, #nomeutilizador');

            let initialValues = {};
            inputs.forEach(input => {
                initialValues[input.id] = input.value;
            });

            function checkValues() {
                let hasChanged = false;
                inputs.forEach(input => {
                    if (input.value !== initialValues[input.id]) {
                        hasChanged = true;
                    }
                });
                saveChangesButton.disabled = !hasChanged;
                if (hasChanged) {
                    saveChangesButton.style.backgroundColor = '#5e3bee';
                    saveChangesButton.style.color = 'white';
                } else {
                    saveChangesButton.style.backgroundColor = '#5e3bee';
                    saveChangesButton.style.color = 'white';
                }
            }

            inputs.forEach(input => {
                input.addEventListener('input', checkValues);
            });
        });
    </script>


    <script>

        function getQueryParam(param) {
        let urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
        }

        // Função para mostrar notificação
        function showNotification(message, type) {
            const notificationElement = document.createElement('div');
            notificationElement.classList.add('notification', type);

            // Adiciona ícones para diferentes tipos de notificação
            const icon = document.createElement('i');
            if (type === 'success') {
                icon.classList.add('fa', 'fa-check-circle');
                notificationElement.style.backgroundColor = '#4CAF50';
            } else if (type === 'error') {
                icon.classList.add('fa', 'fa-times-circle');
                notificationElement.style.backgroundColor = '#f44336';
            }

            // Adiciona texto da notificação
            const text = document.createTextNode(message);
            
            // Adiciona ícone e texto ao elemento de notificação
            notificationElement.appendChild(icon);
            notificationElement.appendChild(text);

            document.body.appendChild(notificationElement);

            // Mostra a notificação com animação
            setTimeout(() => {
                notificationElement.classList.add('show');
            }, 10);

            // Remove a notificação após 5 segundos
            setTimeout(() => {
                notificationElement.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notificationElement);
                }, 500);
            }, 5000);
        }

        // Função para remover os parâmetros da URL
        function removeQueryParams() {
            const url = new URL(window.location);
            url.searchParams.delete('notification');
            url.searchParams.delete('reason');
            window.history.replaceState({}, document.title, url);
        }

        // Executar na carga da página
        window.onload = () => {
            const notification = getQueryParam('notification');
            const reason = getQueryParam('reason');

            if (notification === 'success') {
                if (reason === 'sucessoImagem'){
                    showNotification('A foto de perfil foi alterada com sucesso!', 'success');
                } else if (reason === "sucessoAPP") {
                    showNotification('A palavra-passe foi alterada com sucesso!', 'success');
                } else if (reason === 'updatedProfile') {
                    showNotification('Os dados do perfil foram alterados com sucesso!', 'success');
                }
            } else if (notification === 'error') {
                if (reason === 'erroImagem') {
                    showNotification('Ocorreu um erro ao alterar a foto de perfil, tente novamente mais tarde.', 'error');
                } else if (reason === 'passwordErrada') {
                    showNotification('A password atual introduzida está errada, tente novamente.', 'error');
                } else if (reason === 'passwordDiferentes') {
                    showNotification('As palavras-passes não são iguais, volte a introduzi-las.', 'error');
                } else if (reason === "erroPassword") {
                    showNotification('Ocorreu um erro ao alterar a palavra-passe, tente novamente mais tarde.', 'error');
                } else if (reason === 'updateFailed') {
                    showNotification('Ocorreu um erro ao alterar os dados do perfil, tente novamente mais tarde.', 'error');
                } else if (reason === 'emailExists') {
                    showNotification('O email introduzido já está registado.', 'error');
                } else if (reason === 'usernameExists') {
                    showNotification('O nome de utilizador introduzido já está registado.', 'error');
                }
            }

            removeQueryParams();
        };

    </script>

</body>
</html>
