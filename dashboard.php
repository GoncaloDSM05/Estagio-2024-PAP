<?php
include 'assets/php/conecta.php';
session_start();

if (!isset($_SESSION['idutilizador'])) {
    header("Location: conta.html");
    exit();
}

$idutilizador = $_SESSION['idutilizador'];

$query = $mysqli->prepare("SELECT primeironome, ultimonome, fotoPath FROM utilizadores WHERE idutilizador = ?");
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
$queryGrupo = $mysqli->prepare("SELECT idutilizador FROM utilizadorgrupo WHERE idutilizador = ?");
if ($queryGrupo === false) {
    die("Erro preparando a query de grupo: " . $mysqli->error);
}

$queryGrupo->bind_param("i", $idutilizador);
$queryGrupo->execute();
$resultGrupo = $queryGrupo->get_result();

// Se não estiver em nenhum grupo, $estaEmGrupo será false. Caso contrário, será true.
$estaEmGrupo = $resultGrupo->num_rows > 0;
?>

<?php if (!$estaEmGrupo) : ?>
    <!DOCTYPE html>
    <html lang="pt">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="shortcut icon" type="imagex/png" href="images/logo.png">
        <link rel="stylesheet" href="assets/css/styled.css">
        <title>Dashboard - SquadForge</title>
    </head>

    <body>
        <div class="container">

            <aside class="left-section">
                <div class="logo">
                    <img src="images/logo.png">
                    <a href="dashboard.php">SquadForge</a>
                </div>

            </aside>
            <main>
                <div class="infgroup">
                    <h3>Você não está em nenhum grupo!</h3>
                    <button id="criarGrupo">Criar um Grupo</button>
                    <div id="modalCriarGrupo" class="modal">
                        <div class="modal-content">
                            <span class="close">&times;</span>
                            <h2>Criar um Grupo</h2><br>
                            <p>Criar um grupo é uma decisão importante e requer responsabilidade perante uma futura grande equipa. Preencha todos os dados necessários abaixo!</p><br>
                            <form method="post" action="assets/php/grupo.php">
                                <input type="text" id="nome" name="nome" placeholder="Nome" required><br>
                                <textarea id="diretrizes" name="diretrizes" placeholder="Diretrizes" required></textarea><br>
                                <textarea id="descricao" name="descricao" placeholder="Descrição" required></textarea><br>
                                <input id="enviarCriarGrupo" name="enviarCriarGrupo" type="submit" value="Criar">
                            </form>
                        </div>
                    </div>

                    <button id="entrarGrupo">Entrar num Grupo</button>
                    <div id="modalEntrarGrupo" class="modal">
                        <div class="modal-content">
                            <span class="close">&times;</span>
                            <h2>Entrar num Grupo</h2><br>
                            <p>Vamos entrar num grupo! Tem o código de acesso? Esse código serve como identificação do grupo e é a chave de entrada. Não tem o código? Contacte o criador do grupo.</p><br>
                            <form method="post" action="assets/php/grupo.php">
                                <input type="text" id="codgrupo" name="codgrupo" placeholder="Código do grupo" required><br>
                                <input id="enviarEntrarGrupo" name="enviarEntrarGrupo" type="submit" value="Entrar">
                            </form>
                        </div>
                    </div>

                    <img id="imgGrupo" src="images/grupo.png">
                </div>
            </main>
            <aside class="right-section">
                <div class="top">
                    <div class="notification-container">
                        <i class='bx bx-bell' id="notification-icon"></i>
                        <div class="notification-dropdown" id="notification-dropdown">
                            <!-- Aqui vão as notificações -->
                        </div>
                    </div>
                    <div class="profile">
                        <div class="left">
                            <img src="<?php echo htmlspecialchars($user['fotoPath']) ? 'assets/php/' . $user['fotoPath'] . '?' . time() : 'images/profile.png'; ?>" alt="Foto de perfil" class="profile-photo">
                            <div class="user">
                                <h5><?php echo htmlspecialchars($user['primeironome']) . ' ' . htmlspecialchars($user['ultimonome']); ?></h5>
                            </div>
                        </div>
                        <a href="perfil.php" class="perfil-link"> <i class='bx bxs-chevron-right'></i> </a>
                    </div>

                </div>
            </aside>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    fetchNotifications(); // Verifica e atualiza as notificações quando a página carrega
                });

                document.getElementById("notification-icon").addEventListener("click", function() {
                    const notificationDropdown = document.getElementById("notification-dropdown");
                    notificationDropdown.classList.toggle("active");

                    // Marcar notificações como lidas quando o dropdown for aberto
                    if (notificationDropdown.classList.contains("active")) {
                        markNotificationsAsRead();
                    }
                });

                // Função para recuperar e exibir notificações
                function fetchNotifications() {
                    fetch('assets/php/verificaNotificacoes.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const notifications = data.notifications;
                                const notificationDropdown = document.getElementById('notification-dropdown');
                                notificationDropdown.innerHTML = ''; // Limpa as notificações anteriores
                                notifications.forEach(notification => {
                                    const notificationItem = document.createElement('div');
                                    notificationItem.classList.add('notification-item');
                                    notificationItem.innerHTML = `
                        <p>${notification.mensagem}</p>
                        <span>${notification.created_at}</span>
                    `;
                                    notificationDropdown.appendChild(notificationItem);
                                });

                                // Verificar se há notificações não lidas
                                const notificationIcon = document.getElementById('notification-icon');
                                if (data.unreadCount > 0) {
                                    notificationIcon.classList.add('has-unread');
                                } else {
                                    notificationIcon.classList.remove('has-unread');
                                }
                            } else {
                                console.error('Erro ao recuperar notificações:', data.error);
                            }
                        })
                        .catch(error => console.error('Erro ao processar resposta:', error));
                }

                // Marcar notificações como lidas
                function markNotificationsAsRead() {
                    fetch('assets/php/marcarNotificacoesLidas.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const notificationIcon = document.getElementById('notification-icon');
                                notificationIcon.classList.remove('has-unread');
                            } else {
                                console.error('Erro ao marcar notificações como lidas:', data.error);
                            }
                        })
                        .catch(error => console.error('Erro ao processar resposta:', error));
                }

                // Fecha a caixa de notificações quando clicar fora dela
                window.onclick = function(event) {
                    if (!event.target.matches('#notification-icon')) {
                        const dropdowns = document.getElementsByClassName("notification-dropdown");
                        for (let i = 0; i < dropdowns.length; i++) {
                            const openDropdown = dropdowns[i];
                            if (openDropdown.classList.contains('active')) {
                                openDropdown.classList.remove('active');
                            }
                        }
                    }
                };
            </script>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var modalCriar = document.getElementById("modalCriarGrupo");
                    var modalEntrar = document.getElementById("modalEntrarGrupo");
                    var btnCriar = document.getElementById("criarGrupo");
                    var btnEntrar = document.getElementById("entrarGrupo");
                    var spanCriar = modalCriar.getElementsByClassName("close")[0];
                    var spanEntrar = modalEntrar.getElementsByClassName("close")[0];

                    btnCriar.onclick = function() {
                        modalCriar.style.display = "block";
                    }

                    btnEntrar.onclick = function() {
                        modalEntrar.style.display = "block";
                    }

                    spanCriar.onclick = function() {
                        modalCriar.style.display = "none";
                    }

                    spanEntrar.onclick = function() {
                        modalEntrar.style.display = "none";
                    }

                    window.onclick = function(event) {
                        if (event.target == modalCriar) {
                            modalCriar.style.display = "none";
                        }
                        if (event.target == modalEntrar) {
                            modalEntrar.style.display = "none";
                        }
                    }
                });

                function getQueryParam(param) {
                    let urlParams = new URLSearchParams(window.location.search);
                    return urlParams.get(param);
                }

                function showNotification(message, type) {
                    const notificationElement = document.createElement('div');
                    notificationElement.classList.add('notification', type);
                    const icon = document.createElement('i');
                    if (type === 'success') {
                        icon.classList.add('fa', 'fa-check-circle');
                        notificationElement.style.backgroundColor = '#4CAF50';
                    } else if (type === 'error') {
                        icon.classList.add('fa', 'fa-times-circle');
                        notificationElement.style.backgroundColor = '#f44336';
                    }
                    const text = document.createTextNode(message);
                    notificationElement.appendChild(icon);
                    notificationElement.appendChild(text);
                    document.body.appendChild(notificationElement);
                    setTimeout(() => {
                        notificationElement.classList.add('show');
                    }, 10);
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

                window.addEventListener('load', function() {
                    const notification = getQueryParam('notification');
                    const reason = getQueryParam('reason');

                    if (notification === 'error') {
                        if (reason === 'erroInesperadoC') {
                            showNotification('Ocorreu um erro ao criar o grupo, tente novamente mais tarde.', 'error');
                        } else if (reason === 'erroInesperado') {
                            showNotification('Ocorreu um erro ao entrar no grupo, tente novamente mais tarde.', 'error');
                        } else if (reason === 'codigoInválido') {
                            showNotification('Código do grupo inválido, verifique o código correto e se ele existe.', 'error');
                        }
                    }
                    if (notification === 'success') {
                        if (reason === 'saiuGrupo') {
                            showNotification('Você saiu do grupo com sucesso.', 'success');
                        }
                    }
                    removeQueryParams();
                });
            </script>

    </body>

    </html>
<?php else : ?>

    <?php
    include 'assets/php/conecta.php';

    // Função para buscar informações do grupo e membros
    function buscarGrupoEMembros($userId)
    {
        global $mysqli;

        $query = "SELECT codgrupo FROM utilizadorgrupo WHERE idutilizador = ?";
        $stmt = mysqli_prepare($mysqli, $query);
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($groupResult = mysqli_fetch_assoc($result)) {
            $groupId = $groupResult['codgrupo'];

            // Obter informações do grupo
            $query = "SELECT g.*, u.primeironome AS donoPrimeiroNome, u.ultimonome AS donoUltimoNome FROM grupos g INNER JOIN utilizadores u ON g.idutilizadorDono = u.idutilizador WHERE g.codgrupo = ?";
            $stmt = mysqli_prepare($mysqli, $query);
            mysqli_stmt_bind_param($stmt, 's', $groupId);
            mysqli_stmt_execute($stmt);
            $groupResult = mysqli_stmt_get_result($stmt);
            $group = mysqli_fetch_assoc($groupResult);

            // Verificar se o utilizador atual é o dono do grupo
            $isOwner = ($group['idutilizadorDono'] == $userId);

            // Buscar membros do grupo
            $query = "SELECT u.idutilizador, u.primeironome, u.ultimonome, u.nomeutilizador, u.email, u.fotoPath, u.nomefuncao FROM utilizadores u JOIN utilizadorgrupo ug ON u.idutilizador = ug.idutilizador WHERE ug.codgrupo = ?";
            $stmt = mysqli_prepare($mysqli, $query);
            mysqli_stmt_bind_param($stmt, 's', $groupId);
            mysqli_stmt_execute($stmt);
            $membersResult = mysqli_stmt_get_result($stmt);
            $members = mysqli_fetch_all($membersResult, MYSQLI_ASSOC);

            // Retorna o ID do grupo junto com outras informações
            return array('groupId' => $groupId, 'group' => $group, 'isOwner' => $isOwner, 'members' => $members);
        } else {
            return false;
        }
    }

    // Pegar o ID do utilizador da sessão
    $userId = $_SESSION['idutilizador'];

    // Buscar informações do grupo e membros
    $groupInfo = buscarGrupoEMembros($userId);

    // Verifique se as informações do grupo foram retornadas com sucesso
    if ($groupInfo) {
        // Atribua o groupId fora da função buscarGrupoEMembros
        $groupId = $groupInfo['groupId'];
    } else {
        // Lidar com o caso em que as informações do grupo não foram encontradas
        echo "Não foi possível encontrar informações do grupo.";
        // Defina o groupId como vazio ou algum valor padrão, dependendo do caso
        $groupId = '';
    }

    ?>

    <!DOCTYPE html>
    <html lang="pt">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet'>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/locales-all.min.js"></script>
        <link rel="stylesheet" href="assets/css/styled.css">
        <link rel="shortcut icon" type="imagex/png" href="images/logo.png">
        <title>Dashboard - SquadForge</title>
    </head>

    <body>

        <div class="container">

            <aside class="left-section">
                <div class="logo">
                    <button class="menu-btn" id="menu-close">
                        <i class='bx bx-log-out-circle'></i>
                    </button>
                    <img src="images/logo.png">
                    <a href="dashboard.php">SquadForge</a>
                </div>

                <div class="sidebar">
                    <div class="item" id="active" onclick="changeContent('home')">
                        <i class='bx bx-home-alt-2'></i>
                        <a href="#">Geral</a>
                    </div>
                    <div class="item" onclick="changeContent('chat')">
                        <i class='bx bx-message-square-dots'></i>
                        <a href="#">Chat</a>
                    </div>
                    <div class="item" onclick="changeContent('tasks')">
                        <i class='bx bx-task'></i>
                        <a href="#">Tarefas</a>
                    </div>
                    <div class="item" onclick="changeContent('events')">
                        <i class='bx bx-calendar-event'></i>
                        <a href="#">Eventos</a>
                    </div>
                    <div class="item" onclick="changeContent('settings')">
                        <i class='bx bx-cog'></i>
                        <a href="#">Definições</a>

                    </div>
            </aside>

            <?php
            include 'assets/php/conecta.php';

            // Função para buscar informações do grupo e membros
            function buscarGrupo($userId)
            {
                global $mysqli;

                $query = "SELECT codgrupo FROM utilizadorgrupo WHERE idutilizador = ?";
                $stmt = mysqli_prepare($mysqli, $query);
                mysqli_stmt_bind_param($stmt, 'i', $userId);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($groupResult = mysqli_fetch_assoc($result)) {
                    $groupId = $groupResult['codgrupo'];

                    // Obter informações do grupo
                    $query = "SELECT g.*, u.primeironome AS donoPrimeiroNome, u.ultimonome AS donoUltimoNome FROM grupos g INNER JOIN utilizadores u ON g.idutilizadorDono = u.idutilizador WHERE g.codgrupo = ?";
                    $stmt = mysqli_prepare($mysqli, $query);
                    mysqli_stmt_bind_param($stmt, 's', $groupId);
                    mysqli_stmt_execute($stmt);
                    $groupResult = mysqli_stmt_get_result($stmt);
                    $group = mysqli_fetch_assoc($groupResult);

                    // Verificar se o utilizador atual é o dono do grupo
                    $isOwner = ($group['idutilizadorDono'] == $userId);

                    // Buscar membros do grupo
                    $query = "SELECT u.idutilizador, u.primeironome, u.ultimonome, u.nomeutilizador, u.email, u.fotoPath, u.nomefuncao FROM utilizadores u JOIN utilizadorgrupo ug ON u.idutilizador = ug.idutilizador WHERE ug.codgrupo = ?";
                    $stmt = mysqli_prepare($mysqli, $query);
                    mysqli_stmt_bind_param($stmt, 's', $groupId);
                    mysqli_stmt_execute($stmt);
                    $membersResult = mysqli_stmt_get_result($stmt);
                    $members = mysqli_fetch_all($membersResult, MYSQLI_ASSOC);

                    // Retorna o ID do grupo junto com outras informações
                    return array('groupId' => $groupId, 'group' => $group, 'isOwner' => $isOwner, 'members' => $members);
                } else {
                    return false;
                }
            }

            // Pegar o ID do utilizador da sessão
            $userId = $_SESSION['idutilizador'];

            // Buscar informações do grupo e membros
            $groupInfo = buscarGrupo($userId);

            // Verifique se as informações do grupo foram retornadas com sucesso
            if ($groupInfo) {
                // Atribua o groupId fora da função buscarGrupoEMembros
                $groupId = $groupInfo['groupId'];
            } else {
                // Lidar com o caso em que as informações do grupo não foram encontradas
                echo "Não foi possível encontrar informações do grupo.";
                // Defina o groupId como vazio ou algum valor padrão, dependendo do caso
                $groupId = '';
            }

            // Calcular a data de início e fim da semana atual
            $startOfWeek = date('Y-m-d 00:00:00', strtotime('monday this week'));
            $endOfWeek = date('Y-m-d 23:59:59', strtotime('sunday this week'));

            // Preparar consulta SQL para buscar as tarefas do grupo do utilizador da semana atual
            $sqlTarefas = "SELECT idtarefa, titulo, descricao, datahora, estado FROM tarefasg WHERE codgrupo = ? AND datahora BETWEEN ? AND ?";
            $stmtTarefas = $mysqli->prepare($sqlTarefas);
            $stmtTarefas->bind_param("sss", $groupId, $startOfWeek, $endOfWeek);
            $stmtTarefas->execute();
            $resultadoTarefas = $stmtTarefas->get_result();

            // Preparar consulta SQL para buscar os eventos do grupo do utilizador da semana atual
            $sqlEventos = "SELECT idevento, titulo, cor, inicio, fim FROM eventos WHERE codgrupo = ? AND inicio BETWEEN ? AND ?";
            $stmtEventos = $mysqli->prepare($sqlEventos);
            $stmtEventos->bind_param("sss", $groupId, $startOfWeek, $endOfWeek);
            $stmtEventos->execute();
            $resultadoEventos = $stmtEventos->get_result();

            // Fechar statement
            $stmtTarefas->close();
            $stmtEventos->close();
            ?>

            <div id="home" class="content active">
                <main>
                    <header>
                        <button class="menu-btn" id="menu-open">
                            <i class='bx bx-menu'></i>
                        </button>
                        <h5>Olá <b><?php echo htmlspecialchars($user['primeironome']); ?></b>, bem vindo/a de volta!</h5>
                    </header>
                    <br><br>
                    <div id="tarefas-section" class="dashboard-section">
                        <!-- Tarefas do Grupo -->
                        <div class="section-header">
                            <h2>Tarefas da Semana</h2>
                        </div>
                        <ul class="dashboard-list">
                            <?php if ($resultadoTarefas->num_rows > 0) : ?>
                                <?php while ($row = $resultadoTarefas->fetch_assoc()) : ?>
                                    <li class="list-item">
                                        <span class="item-title"><?php echo htmlspecialchars($row['titulo']); ?></span>
                                        <span class="item-status"><?php echo htmlspecialchars($row['estado']); ?></span>
                                    </li>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <li class="list-item empty">Nenhuma tarefa encontrada para esta semana.</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div id="eventos-section" class="dashboard-section">
                        <!-- Eventos do Grupo -->
                        <div class="section-header">
                            <h2>Eventos da Semana</h2>
                        </div>
                        <ul class="dashboard-list">
                            <?php if ($resultadoEventos->num_rows > 0) : ?>
                                <?php while ($row = $resultadoEventos->fetch_assoc()) : ?>
                                    <li class="list-item">
                                        <span class="item-title"><?php echo htmlspecialchars($row['titulo']); ?></span>
                                        <span class="item-status" style="background-color: <?php echo htmlspecialchars($row['cor']); ?>;"><?php echo htmlspecialchars($row['cor']); ?></span>
                                    </li>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <li class="list-item empty">Nenhum evento encontrado para esta semana.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </main>
            </div>

            <div id="chat" class="content">
                <div id="chat-container">
                    <div id="left-panel">
                        <div id="chat-box"></div>
                        <div id="input-container">
                            <input type="text" id="message" placeholder="Digite a sua mensagem">
                            <button id="send" data-tooltip="Enviar mensagem"><i class="fas fa-paper-plane icon"></i></button>
                        </div>
                    </div>
                </div>

                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script>
                    $(document).ready(function() {
                        var userId = <?php echo $idutilizador; ?>;
                        var autoScroll = true;

                        function loadMessages() {
                            var user = userId;
                            $.ajax({
                                url: 'assets/php/verificaChat.php',
                                method: 'GET',
                                data: {
                                    user: user
                                },
                                success: function(data) {
                                    $('#chat-box').html(data);
                                    if (autoScroll) {
                                        $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
                                    }
                                }
                            });
                        }

                        $('#send').click(function() {
                            var message = $('#message').val();
                            if (message.trim() !== "") {
                                $.ajax({
                                    url: 'assets/php/verificaChat.php',
                                    method: 'POST',
                                    data: {
                                        action: 'send',
                                        user: userId,
                                        message: message
                                    },
                                    success: function(data) {
                                        $('#message').val("");
                                        loadMessages();
                                    }
                                });
                            }
                        });

                        $('#message').on('keypress', function(event) {
                            if (event.which === 13) {
                                $('#send').click();
                            }
                        });

                        $('#chat-box').on('scroll', function() {
                            if ($('#chat-box').scrollTop() <= 0) {
                                autoScroll = false;
                            } else if ($('#chat-box').scrollTop() + $('#chat-box').innerHeight() >= $('#chat-box')[0].scrollHeight - 10) {
                                autoScroll = true;
                            } else {
                                autoScroll = false;
                            }
                        });

                        setInterval(loadMessages, 1000);
                        loadMessages();
                    });
                </script>
            </div>

            <div id="tasks" class="content">
                <h1>Gerenciador de Tarefas</h1>
                <button id="openModalButton">Adicionar Tarefa</button>
                <button id="openCompletedTasksModalButton">Ver Tarefas Terminadas</button>
                <select id="sortTasks" class="status-dropdown">
                    <option value="all">Todas as Tarefas</option>
                    <option value="dueSoon">Próximas do Prazo</option>
                    <option value="thisWeek">Tarefas Desta Semana</option>
                </select>

                <div id="taskModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Criar Nova Tarefa</h2>
                            <span class="close" onclick="closeModal('taskModal')">&times;</span>
                        </div>
                        <div class="modal-body">
                            <form id="taskForm" action="assets/php/verificaTarefas.php" method="post">
                                <label for="title">Título:</label>
                                <input type="text" id="title" name="title" required>

                                <label for="description">Descrição:</label>
                                <textarea id="description" name="description" required></textarea>

                                <label for="dueDate">Data de Término:</label>
                                <input type="date" id="dueDate" name="dueDate" required>

                                <label for="dueTime">Hora de Término:</label>
                                <input type="time" id="dueTime" name="dueTime" required>

                                <button type="submit">Adicionar Tarefa</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="completedTasksModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Tarefas Terminadas</h2>
                            <span class="close" onclick="closeModal('completedTasksModal')">&times;</span>
                        </div>
                        <div class="modal-body">
                            <ul id="completedTaskList">
                                <!-- Tarefas terminadas serão inseridas aqui -->
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="task-list">
                    <h2>Lista de Tarefas</h2>
                    <ul id="taskList">
                        <!-- As tarefas serão inseridas aqui -->
                    </ul>
                </div>

                <script>
                    document.getElementById('openModalButton').addEventListener('click', function() {
                        document.getElementById('taskModal').style.display = 'block';
                    });

                    document.getElementById('openCompletedTasksModalButton').addEventListener('click', function() {
                        document.getElementById('completedTasksModal').style.display = 'block';
                    });

                    window.onclick = function(event) {
                        if (event.target.className === 'modal') {
                            event.target.style.display = 'none';
                        }
                    };

                    function closeModal(modalId) {
                        document.getElementById(modalId).style.display = 'none';
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        const taskList = document.getElementById('taskList');
                        const completedTaskList = document.getElementById('completedTaskList');
                        const sortTasks = document.getElementById('sortTasks');

                        sortTasks.addEventListener('change', fetchTasks);

                        // Adiciona evento de clique para remover tarefas usando formulário
                        taskList.addEventListener('click', function(event) {
                            if (event.target.classList.contains('remove-task')) {
                                const idtarefa = event.target.dataset.idtarefa;
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = 'assets/php/verificaTarefas.php';

                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'idtarefa';
                                input.value = idtarefa;

                                form.appendChild(input);
                                document.body.appendChild(form);
                                form.submit();
                            }
                        });

                        completedTaskList.addEventListener('click', function(event) {
                            if (event.target.classList.contains('remove-task')) {
                                const idtarefa = event.target.dataset.idtarefa;
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = 'assets/php/verificaTarefas.php';

                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'idtarefa';
                                input.value = idtarefa;

                                form.appendChild(input);
                                document.body.appendChild(form);
                                form.submit();
                            }
                        });

                        // Adiciona evento de mudança para atualizar o estado da tarefa usando fetch
                        taskList.addEventListener('change', function(event) {
                            if (event.target.classList.contains('status-dropdown')) {
                                const idtarefa = event.target.dataset.idtarefa;
                                const estado = event.target.value;
                                updateTaskStatus(idtarefa, estado);
                            }
                        });

                        completedTaskList.addEventListener('change', function(event) {
                            if (event.target.classList.contains('status-dropdown')) {
                                const idtarefa = event.target.dataset.idtarefa;
                                const estado = event.target.value;
                                updateTaskStatus(idtarefa, estado);
                            }
                        });

                        // Função para atualizar o estado da tarefa
                        function updateTaskStatus(idtarefa, estado) {
                            fetch('assets/php/atualizarEstadoTarefa.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: 'idtarefa=' + encodeURIComponent(idtarefa) + '&estado=' + encodeURIComponent(estado),
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Erro ao atualizar o estado da tarefa');
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    if (!data.success) {
                                        console.error('Erro: ', data.error);
                                    } else {
                                        fetchTasks(); // Recarregar as tarefas após a atualização do estado
                                    }
                                })
                                .catch(error => {
                                    console.error('Erro ao atualizar o estado da tarefa:', error);
                                });
                        }

                        // Função para buscar e exibir tarefas
                        function fetchTasks() {
                            fetch('assets/php/listarTarefas.php')
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Erro ao buscar as tarefas');
                                    }
                                    return response.json();
                                })
                                .then(tasks => {
                                    taskList.innerHTML = '';
                                    completedTaskList.innerHTML = '';
                                    const now = new Date();
                                    const filter = sortTasks.value;

                                    const filteredTasks = tasks.filter(task => {
                                        const taskDate = new Date(task.datahora);

                                        if (task.estado === 'terminada') {
                                            const taskItem = createTaskItem(task);
                                            completedTaskList.appendChild(taskItem);
                                            return false;
                                        }

                                        if (filter === 'dueSoon') {
                                            return taskDate > now && taskDate <= new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
                                        } else if (filter === 'thisWeek') {
                                            const startOfWeek = new Date(now);
                                            startOfWeek.setDate(now.getDate() - now.getDay());
                                            const endOfWeek = new Date(startOfWeek);
                                            endOfWeek.setDate(startOfWeek.getDate() + 7);
                                            return taskDate >= startOfWeek && taskDate <= endOfWeek;
                                        } else {
                                            return true;
                                        }
                                    });

                                    filteredTasks.sort((a, b) => new Date(a.datahora) - new Date(b.datahora));

                                    filteredTasks.forEach(task => {
                                        const taskItem = createTaskItem(task);
                                        taskList.appendChild(taskItem);
                                    });
                                })
                                .catch(error => {
                                    console.error('Erro ao buscar as tarefas:', error);
                                });
                        }

                        function createTaskItem(task) {
                            const taskItem = document.createElement('li');
                            taskItem.id = 'taskItem-' + task.idtarefa;
                            const taskDate = new Date(task.datahora);
                            const now = new Date();
                            const isOverdue = taskDate < now && task.estado !== 'terminada';

                            taskItem.innerHTML = `
                            <span class="title">${task.titulo}</span>
                            <span class="description">${task.descricao}</span>
                            <span class="dueDate ${isOverdue ? 'overdue' : ''}">${taskDate.toLocaleString()}</span>
                            <select class="status-dropdown" data-idtarefa="${task.idtarefa}">
                                <option value="criada" ${task.estado === 'criada' ? 'selected' : ''}>Criada</option>
                                <option value="em_progresso" ${task.estado === 'em_progresso' ? 'selected' : ''}>Em Progresso</option>
                                <option value="terminada" ${task.estado === 'terminada' ? 'selected' : ''}>Terminada</option>
                            </select>
                            <button class="remove-task" data-idtarefa="${task.idtarefa}">Remover</button>
                        `;

                            return taskItem;
                        }

                        fetchTasks();
                    });
                </script>
            </div>

            <div id="events" class="content">
                <div id="calendar-container">
                    <div id='calendar'></div>
                </div>
            </div>

            <div id="event-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Adicionar Evento</h2>
                        <span class="close" onclick="closeModal('event-modal')">&times;</span>
                    </div>
                    <div class="modal-body">
                        <form id="event-form" action="assets/php/verificaEvento.php" method="POST">
                            <input type="hidden" name="action" value="addEvent">
                            <input type="hidden" id="selectedDate" name="selectedDate">

                            <label for="title">Título do Evento:</label>
                            <input type="text" id="title" name="title" required>

                            <label for="color">Cor:</label>
                            <input type="color" id="color" name="color" value="#0000ff" required>

                            <!-- Campos de hora -->
                            <label for="startHour">Hora de Início:</label>
                            <input type="time" id="startHour" name="startHour" required>

                            <label for="endHour">Hora de Término:</label>
                            <input type="time" id="endHour" name="endHour" required>

                            <button type="submit">Adicionar Evento</button>
                        </form>
                    </div>
                </div>
            </div>

            <div id="event-info-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Detalhes do Evento</h2>
                        <span class="close" onclick="closeModal('event-info-modal')">&times;</span>
                    </div>
                    <div class="modal-body">
                        <form id="event-info-form" action="assets/php/verificaEvento.php" method="POST">
                            <input type="hidden" id="event-id" name="id">
                            <label for="event-title">Título do Evento:</label>
                            <input type="text" id="event-title" name="title" required>

                            <label for="event-color">Cor:</label>
                            <input type="color" id="event-color" name="color" required>

                            <label for="event-start">Hora de Início:</label>
                            <input type="datetime-local" id="event-start" name="start" required>

                            <label for="event-end">Hora de Término:</label>
                            <input type="datetime-local" id="event-end" name="end" required>

                            <button type="submit">Salvar</button>
                            <button type="button" onclick="removeEvent()">Remover</button>
                        </form>
                    </div>
                </div>
            </div>


            <div id="settings" class="content">

                <div class="settings-container">
                    <div class="group-header">
                        <h1 id="groupName"><?php echo htmlspecialchars($groupInfo['group']['nome']); ?></h1>
                        <?php if ($groupInfo['isOwner']) : ?>
                            <div id="inviteCode">Código de Convite: <span id="groupCode"><?php echo htmlspecialchars($groupInfo['group']['codgrupo']); ?></span><span onclick="copyCode()" class="ml-2"><i class="fa fa-copy"></i></span></div>
                            <button class="edit" onclick="openEditModal()">Editar</button>
                        <?php endif; ?>
                    </div>
                    <div class="group-info">
                        <p><strong>Diretrizes:</strong> <span id="groupGuidelines"><?php echo nl2br(htmlspecialchars($groupInfo['group']['diretrizes'])); ?></span></p>
                        <p><strong>Descrição:</strong> <span id="groupDescription"><?php echo nl2br(htmlspecialchars($groupInfo['group']['descricao'])); ?></span></p>
                        <strong>Dono do Grupo:</strong> <?php echo htmlspecialchars($groupInfo['group']['donoPrimeiroNome'] . ' ' . $groupInfo['group']['donoUltimoNome']); ?>
                    </div>
                    <br>
                    <?php if ($groupInfo['isOwner']) : ?>
                        <button class="invite" onclick="openInviteModal()">Enviar Convite</button>
                    <?php endif; ?>
                    <button class="invite" onclick="openExitModal()" style="background-color: red; color: white;">Sair do Grupo</button>
                    <div class="group-members">
                        <h2>Membros do Grupo</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Foto</th>
                                    <th>Nome</th>
                                    <?php if ($groupInfo['isOwner']) : ?>
                                        <th class="actions">Ações</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($groupInfo['members'] as $member) : ?>
                                    <tr>
                                        <td class="user-info" onclick="showUserInfo('<?php echo htmlspecialchars($member['primeironome']) . ' ' . htmlspecialchars($member['ultimonome']); ?>', '<?php echo htmlspecialchars($member['nomefuncao']); ?>', '<?php echo htmlspecialchars($member['nomeutilizador']); ?>', '<?php echo htmlspecialchars($member['email']); ?>', '<?php echo htmlspecialchars($member['fotoPath']); ?>')">
                                            <img src="<?php echo htmlspecialchars($member['fotoPath']) ? 'assets/php/' . htmlspecialchars($member['fotoPath']) . '?' . time() : 'images/profile.png'; ?>" style="width: 50px; height: 50px;">
                                        </td>
                                        <td><?php echo htmlspecialchars($member['primeironome']) . ' ' . htmlspecialchars($member['ultimonome']); ?></td>
                                        <?php if ($groupInfo['isOwner']) : ?>
                                            <td class="actions">
                                                <button class="danger" onclick="confirmRemoveMember(<?php echo $member['idutilizador']; ?>)">Remover</button>
                                                <button class="primary" onclick="confirmTransferOwnership(<?php echo $member['idutilizador']; ?>)">Transferir Admin</button>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Edit Group Modal -->
                <div id="editGroupModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Editar Informações do Grupo</h2>
                            <span class="close" onclick="closeModal('editGroupModal')">&times;</span>
                        </div>
                        <div class="modal-body">
                            <form id="editGroupForm" method="post" action="assets/php/verificaDefinicoes.php">
                                <input type="hidden" name="action" value="editGroup">
                                <label for="groupNameInput">Nome do Grupo:</label>
                                <input type="text" id="groupNameInput" name="groupName" value="<?php echo htmlspecialchars($groupInfo['group']['nome']); ?>" required>
                                <label for="groupGuidelinesInput">Diretrizes:</label>
                                <textarea id="groupGuidelinesInput" name="groupGuidelines" required><?php echo htmlspecialchars($groupInfo['group']['diretrizes']); ?></textarea>
                                <label for="groupDescriptionInput">Descrição:</label>
                                <textarea id="groupDescriptionInput" name="groupDescription" required><?php echo htmlspecialchars($groupInfo['group']['descricao']); ?></textarea>
                                <button type="submit" class="primary">Salvar</button>
                            </form>
                        </div>
                    </div>
                </div>


                <!-- User Info Modal -->
                <div id="userModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Informações do Membro</h2>
                            <span class="close" onclick="closeModal('userModal')">&times;</span>
                        </div>
                        <div class="modal-body">
                            <img id="modalUserPhoto" src="" alt="Foto do Utilizador" style="width: 70px; height: 70px;">
                            <br>
                            <p id="modalUserName"></p>
                            <br>
                            <p id="modalUserFunction"></p>
                            <br>
                            <p id="modalUserUsername"></p>
                            <p id="modalUserEmail"></p>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Modals -->
                <div id="confirmRemoveModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Confirmar Remoção</h2>
                            <span class="close" onclick="closeModal('confirmRemoveModal')">&times;</span>
                        </div>
                        <div class="modal-body">
                            <form id="removeMemberForm" method="post" action="assets/php/verificaDefinicoes.php">
                                <input type="hidden" name="action" value="removeMember">
                                <input type="hidden" id="removeUserId" name="userId">
                                <label for="removePassword">Palavra-passe:</label>
                                <input type="password" id="removePassword" name="password" required class="styled-password-input">
                                <button type="submit" class="danger">Remover</button>
                                <button type="button" class="secondary" onclick="closeModal('confirmRemoveModal')">Cancelar</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="confirmTransferModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Confirmar Transferência de Admin</h2>
                            <span class="close" onclick="closeModal('confirmTransferModal')">&times;</span>
                        </div>
                        <div class="modal-body">
                            <form id="transferOwnershipForm" method="post" action="assets/php/verificaDefinicoes.php">
                                <input type="hidden" name="action" value="transferOwnership">
                                <input type="hidden" id="transferUserId" name="userId">
                                <label for="transferPassword">Palavra-passe:</label>
                                <input type="password" id="transferPassword" name="password" required class="styled-password-input">
                                <button type="submit" class="primary">Transferir</button>
                                <button type="button" class="secondary" onclick="closeModal('confirmTransferModal')">Cancelar</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal de Envio de Convite por Email -->
                <div id="inviteModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Enviar Convite por Email</h2>
                            <span class="close" onclick="closeModal('inviteModal')">&times;</span>
                        </div>
                        <div class="modal-body">
                            <form id="inviteForm" method="post" action="assets/php/enviar_convite.php">
                                <input type="hidden" name="groupId" value="<?php echo htmlspecialchars($groupInfo['group']['codgrupo']); ?>">
                                <label for="inviteEmail">Email do destinatário:</label>
                                <input type="email" id="inviteEmail" name="email" required class="styled-password-input">
                                <button type="submit" class="primary">Enviar Convite</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Exit -->
                <div id="confirmExitGroup" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Confirmar de Saída do Grupo</h2>
                            <span class="close" onclick="closeModal('confirmExitGroup')">&times;</span>
                        </div>
                        <div class="modal-body">
                            <form id="exitMemberForm" method="post" action="assets/php/verificaDefinicoes.php">
                                <input type="hidden" name="action" value="exitMember">
                                <input type="hidden" id="exitUserId" name="userId">
                                <label for="removePassword">Deseja sair do grupo?</label>
                                <button type="submit" class="danger">Sair</button>
                                <button type="button" class="secondary" onclick="closeModal('confirmExitGroup')">Cancelar</button>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    function showUserInfo(name, funcao, nomeUsuario, email, foto) {
                        document.getElementById('modalUserName').textContent = 'Nome: ' + name;
                        document.getElementById('modalUserFunction').textContent = 'Função: ' + funcao;
                        document.getElementById('modalUserUsername').textContent = 'Nome de Utilizador: ' + nomeUsuario;
                        document.getElementById('modalUserEmail').textContent = 'Email: ' + email;
                        document.getElementById('modalUserPhoto').src = 'assets/php/' + foto;
                        document.getElementById('userModal').style.display = 'block';
                    }

                    function openInviteModal() {
                        document.getElementById('inviteModal').style.display = 'block';
                    }

                    function openExitModal() {
                        document.getElementById('confirmExitGroup').style.display = 'block';
                    }

                    function openEditModal() {
                        document.getElementById('editGroupModal').style.display = 'block';
                    }

                    function confirmRemoveMember(userId) {
                        document.getElementById('removeUserId').value = userId;
                        document.getElementById('confirmRemoveModal').style.display = 'block';
                    }

                    function confirmTransferOwnership(userId) {
                        document.getElementById('transferUserId').value = userId;
                        document.getElementById('confirmTransferModal').style.display = 'block';
                    }

                    function closeModal(modalId) {
                        document.getElementById(modalId).style.display = 'none';
                    }

                    function copyCode() {
                        const code = document.getElementById('groupCode').textContent;
                        navigator.clipboard.writeText(code).then(() => {
                            alert('Código copiado para a área de transferência');
                        }, () => {
                            alert('Falha ao copiar o código');
                        });
                    }

                    window.onclick = function(event) {
                        if (event.target.className === 'modal') {
                            event.target.style.display = 'none';
                        }
                    }
                </script>

            </div>

            <?php
            include 'assets/php/conecta.php';

            // Consulta SQL para contar o número de tarefas criadas
            $sqlTarefasCriadas = "SELECT COUNT(*) AS totalCriadas FROM tarefasg WHERE codgrupo = ?";
            $stmtTarefasCriadas = $mysqli->prepare($sqlTarefasCriadas);
            $stmtTarefasCriadas->bind_param('s', $groupId);
            $stmtTarefasCriadas->execute();
            $resultTarefasCriadas = $stmtTarefasCriadas->get_result();
            $rowTarefasCriadas = $resultTarefasCriadas->fetch_assoc();
            $totalTarefasCriadas = $rowTarefasCriadas['totalCriadas'];

            // Consulta SQL para contar o número de tarefas finalizadas
            $sqlTarefasFinalizadas = "SELECT COUNT(*) AS totalFinalizadas FROM tarefasg WHERE codgrupo = ? AND estado = 'terminada'";
            $stmtTarefasFinalizadas = $mysqli->prepare($sqlTarefasFinalizadas);
            $stmtTarefasFinalizadas->bind_param('s', $groupId);
            $stmtTarefasFinalizadas->execute();
            $resultTarefasFinalizadas = $stmtTarefasFinalizadas->get_result();
            $rowTarefasFinalizadas = $resultTarefasFinalizadas->fetch_assoc();
            $totalTarefasFinalizadas = $rowTarefasFinalizadas['totalFinalizadas'];

            // Consulta SQL para contar o número de eventos criados
            $sqlEventosCriados = "SELECT COUNT(*) AS totalCriados FROM eventos WHERE codgrupo = ?";
            $stmtEventosCriados = $mysqli->prepare($sqlEventosCriados);
            $stmtEventosCriados->bind_param('s', $groupId);
            $stmtEventosCriados->execute();
            $resultEventosCriados = $stmtEventosCriados->get_result();
            $rowEventosCriados = $resultEventosCriados->fetch_assoc();
            $totalEventosCriados = $rowEventosCriados['totalCriados'];

            // Consulta SQL para contar o número de eventos finalizados
            $sqlEventosFinalizados = "SELECT COUNT(*) AS totalFinalizados FROM eventos WHERE codgrupo = ? AND fim < NOW()";
            $stmtEventosFinalizados = $mysqli->prepare($sqlEventosFinalizados);
            $stmtEventosFinalizados->bind_param('s', $groupId);
            $stmtEventosFinalizados->execute();
            $resultEventosFinalizados = $stmtEventosFinalizados->get_result();
            $rowEventosFinalizados = $resultEventosFinalizados->fetch_assoc();
            $totalEventosFinalizados = $rowEventosFinalizados['totalFinalizados'];
            ?>



            <aside class="right-section">
                <div class="top">
                    <div class="notification-container">
                        <i class='bx bx-bell' id="notification-icon"></i>
                        <div class="notification-dropdown" id="notification-dropdown">
                            <!-- Aqui vão as notificações -->
                        </div>
                    </div>
                    <div class="profile">
                        <div class="left">
                            <img src="<?php echo htmlspecialchars($user['fotoPath']) ? 'assets/php/' . $user['fotoPath'] . '?' . time() : 'images/profile.png'; ?>" alt="Foto de perfil" class="profile-photo">
                            <div class="user">
                                <h5><?php echo htmlspecialchars($user['primeironome']) . ' ' . htmlspecialchars($user['ultimonome']); ?></h5>
                            </div>
                        </div>
                        <a href="perfil.php" class="perfil-link"> <i class='bx bxs-chevron-right'></i> </a>
                    </div>
                </div>
                <div class="separator" id="first">
                    <h4>Estatísticas do Grupo</h4>
                </div>
                <div class="stats">
                    <div class="item">
                        <div class="top">
                            <p>Tarefas</p>
                            <p>Criadas</p>
                        </div>
                        <div class="bottom">
                            <div class="line"></div>
                            <h3><?php echo $totalTarefasCriadas; ?></h3>
                        </div>
                    </div>
                    <div class="item">
                        <div class="top">
                            <p>Eventos</p>
                            <p>Criados</p>
                        </div>
                        <div class="bottom">
                            <div class="line"></div>
                            <h3><?php echo $totalEventosCriados; ?></h3>
                        </div>
                    </div>
                    <div class="item">
                        <div class="top">
                            <p>Tarefas</p>
                            <p>Finalizadas</p>
                        </div>
                        <div class="bottom">
                            <div class="line"></div>
                            <h3><?php echo $totalTarefasFinalizadas; ?></h3>
                        </div>
                    </div>
                    <div class="item">
                        <div class="top">
                            <p>Eventos</p>
                            <p>Finalizados</p>
                        </div>
                        <div class="bottom">
                            <div class="line"></div>
                            <h3><?php echo $totalEventosFinalizados; ?></h3>
                        </div>
                    </div>
                </div>

            </aside>

        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                fetchNotifications(); // Verifica e atualiza as notificações quando a página carrega
            });

            document.getElementById("notification-icon").addEventListener("click", function() {
                const notificationDropdown = document.getElementById("notification-dropdown");
                notificationDropdown.classList.toggle("active");

                // Marcar notificações como lidas quando o dropdown for aberto
                if (notificationDropdown.classList.contains("active")) {
                    markNotificationsAsRead();
                }
            });

            // Função para recuperar e exibir notificações
            function fetchNotifications() {
                fetch('assets/php/verificaNotificacoes.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const notifications = data.notifications;
                            const notificationDropdown = document.getElementById('notification-dropdown');
                            notificationDropdown.innerHTML = ''; // Limpa as notificações anteriores
                            notifications.forEach(notification => {
                                const notificationItem = document.createElement('div');
                                notificationItem.classList.add('notification-item');
                                notificationItem.innerHTML = `
                        <p>${notification.mensagem}</p>
                        <span>${notification.created_at}</span>
                    `;
                                notificationDropdown.appendChild(notificationItem);
                            });

                            // Verificar se há notificações não lidas
                            const notificationIcon = document.getElementById('notification-icon');
                            if (data.unreadCount > 0) {
                                notificationIcon.classList.add('has-unread');
                            } else {
                                notificationIcon.classList.remove('has-unread');
                            }
                        } else {
                            console.error('Erro ao recuperar notificações:', data.error);
                        }
                    })
                    .catch(error => console.error('Erro ao processar resposta:', error));
            }

            // Marcar notificações como lidas
            function markNotificationsAsRead() {
                fetch('assets/php/marcarNotificacoesLidas.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const notificationIcon = document.getElementById('notification-icon');
                            notificationIcon.classList.remove('has-unread');
                        } else {
                            console.error('Erro ao marcar notificações como lidas:', data.error);
                        }
                    })
                    .catch(error => console.error('Erro ao processar resposta:', error));
            }

            // Fecha a caixa de notificações quando clicar fora dela
            window.onclick = function(event) {
                if (!event.target.matches('#notification-icon')) {
                    const dropdowns = document.getElementsByClassName("notification-dropdown");
                    for (let i = 0; i < dropdowns.length; i++) {
                        const openDropdown = dropdowns[i];
                        if (openDropdown.classList.contains('active')) {
                            openDropdown.classList.remove('active');
                        }
                    }
                }
            };
        </script>

        <script>
            var calendarInitialized = false;

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelector('.item[onclick="changeContent(\'events\')"]').addEventListener('click', function() {
                    changeContent('events');
                    initializeCalendar();
                    setTimeout(clickTodayButton, 500);
                });
            });

            function updateEvent(event) {
                fetch('assets/php/verificaEvento.php?action=updateEvent', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + encodeURIComponent(event.id) +
                            '&title=' + encodeURIComponent(event.title) +
                            '&start=' + encodeURIComponent(event.start.toISOString()) +
                            '&end=' + encodeURIComponent(event.end ? event.end.toISOString() : '') +
                            '&color=' + encodeURIComponent(event.backgroundColor),
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erro ao atualizar o evento');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            console.error('Erro: ', data.error);
                        } else {
                            console.log('Evento atualizado com sucesso');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao atualizar o evento:', error);
                    });
            }

            function initializeCalendar() {
                if (calendarInitialized) return;
                var calendarEl = document.getElementById('calendar');

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'pt-pt',
                    timeZone: 'Europe/Lisbon', // Correção do fuso horário
                    selectable: true,
                    editable: true,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridDay'
                    },
                    buttonText: {
                        today: 'Hoje',
                        month: 'Mês',
                        week: 'Semana',
                        day: 'Dia',
                        list: 'Lista'
                    },
                    select: function(info) {
                        var today = new Date().setHours(0, 0, 0, 0);
                        var selectedDate = new Date(info.startStr).setHours(0, 0, 0, 0);
                        if (selectedDate < today) {
                            alert("Não pode adicionar eventos em datas anteriores a hoje.");
                            calendar.unselect();
                            return;
                        }

                        document.getElementById('selectedDate').value = info.startStr;
                        document.getElementById("event-modal").style.display = "block";
                    },
                    events: 'assets/php/verificaEvento.php?action=fetch_events',
                    eventDidMount: function(info) {
                        info.el.style.backgroundColor = info.event.backgroundColor;
                        info.el.style.color = info.event.textColor || 'white';
                    },
                    eventDrop: function(info) {
                        var today = new Date().setHours(0, 0, 0, 0);
                        var start = new Date(info.event.start).setHours(0, 0, 0, 0);
                        if (start < today) {
                            alert("Não pode mover eventos para datas anteriores a hoje.");
                            info.revert();
                        } else {
                            updateEvent(info.event);
                        }
                    },
                    eventResize: function(info) {
                        var today = new Date().setHours(0, 0, 0, 0);
                        var start = new Date(info.event.start).setHours(0, 0, 0, 0);
                        if (start < today) {
                            alert("Não pode redimensionar eventos para datas anteriores a hoje.");
                            info.revert();
                        } else {
                            updateEvent(info.event);
                        }
                    },
                    eventConstraint: {
                        start: '00:00', 
                        end: '24:00' 
                    },
                    eventAllow: function(dropInfo, draggedEvent) {
                        var today = new Date().setHours(0, 0, 0, 0);
                        var start = new Date(dropInfo.start).setHours(0, 0, 0, 0);
                        if (start < today) {
                            alert("Não pode mover eventos para datas anteriores a hoje.");
                            return false;
                        }
                        return true;
                    },
                    eventDrop: function(info) {
                        var today = new Date();
                        var selectedDate = new Date(info.event.start);
                        if (selectedDate < today) {
                            alert("Não pode arrastar eventos para datas anteriores a hoje.");
                            info.revert();
                        } else {
                            updateEvent(info.event);
                        }
                    }
                });

                calendar.render();
                calendarInitialized = true;

            }

            function closeModal(modalId) {
                document.getElementById(modalId).style.display = 'none';
            }

            function changeContent(contentId) {
                document.querySelectorAll('.content').forEach(function(content) {
                    content.classList.remove('active');
                });

                var activeContent = document.getElementById(contentId);
                if (activeContent) {
                    activeContent.classList.add('active');
                }

                if (calendarInitialized) {
                    clickTodayButton();
                } else {
                    console.log("Calendário não inicializado!");
                }
            }

            function clickTodayButton() {
                var todayButton = document.querySelector('.fc-button-today');
                if (todayButton) {
                    todayButton.click();
                }
            }

            window.onclick = function(event) {
                if (event.target.className === 'modal') {
                    event.target.style.display = 'none';
                }
            }
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
                    if (reason === 'criouGrupo') {
                        showNotification('Grupo criado com sucesso! Vá às definições e partilhe o código.', 'success');
                    } else if (reason === 'entrouGrupo') {
                        showNotification('Você entrou no grupo com sucesso! Bom trabalho.', 'success');
                    } else if (reason === 'atualizouGrupo') {
                        showNotification('Os dados do grupo foram atualizados com sucesso.', 'success');
                    } else if (reason === 'membroRemovido') {
                        showNotification('O membro foi removido com sucesso.', 'success');
                    } else if (reason === 'transferiuAdmin') {
                        showNotification('Administração transferida com sucesso.', 'success');
                    } else if (reason === 'conviteEnviado') {
                        showNotification('O convite foi enviado com sucesso.', 'success');
                    } else if (reason === 'tarefaCriada') {
                        showNotification('Tarefa criada com sucesso.', 'success');
                    } else if (reason === 'tarefaRemovida') {
                        showNotification('Tarefa removida com sucesso.', 'success');
                    } else if (reason === 'eventoCriado') {
                        showNotification('Evento criado com sucesso.', 'success');
                    }

                } else if (notification === 'error') {
                    if (reason === 'erroAtualizaGrupo') {
                        showNotification('Ocorreu um erro ao atualizar os dados do grupo, tente novamente mais tarde.', 'error');
                    } else if (reason === 'ppIncorreta') {
                        showNotification('A palavra-passe introduzida está incorreta, tente introduzir novamente.', 'error');
                    } else if (reason === 'erroMembroRemovido') {
                        showNotification('Ocorreu um erro ao remover o membro, tente novamente mais tarde.', 'error');
                    } else if (reason === 'erroTransferiuAdmin') {
                        showNotification('Ocorreu um erro ao transferir a administração, tente novamente mais tarde.', 'error');
                    } else if (reason === 'naoPodeRemoverDono') {
                        showNotification('Não é possível remover o dono do grupo.', 'error');
                    } else if (reason === 'naoPodeTransferirDono') {
                        showNotification('O membro selecionado já tem permissão de administração.', 'error');
                    } else if (reason === 'erroEnvioConvite') {
                        showNotification('Ocorreu um erro ao enviar o convite, tente novamente mais tarde.', 'error');
                    } else if (reason === 'emailNaoRegistrado') {
                        showNotification('O email introduzido não está registado na SquadForge, tente introduzir novamente.', 'error');
                    } else if (reason === 'codigoInvalido') {
                        showNotification('O código utilizado é inválido, tente novamente mais tarde.', 'error');
                    } else if (reason === 'saidaDono') {
                        showNotification('Você não pode sair do grupo porque é o dono.', 'error');
                    } else if (reason === 'erroSaiuGrupo') {
                        showNotification('Ocorreu um erro ao sair do grupo, tente novamente mais tarde.', 'error');
                    } else if (reason === 'falhaInserirTarefa' || reason === 'erroPrepararConsulta') {
                        showNotification('Ocorreu um erro ao criar a tarefa, tente novamente mais tarde.', 'error');
                    } else if (reason === 'falhaRemoverTarefa') {
                        showNotification('Ocorreu um erro ao remover a tarefa, tente novamente mais tarde.', 'error');
                    } else if (reason === 'dataInvalida') {
                        showNotification('A data introduzida é inválida, tente novamente.', 'error');
                    } else if (reason === 'dataInvalida2') {
                        showNotification('A data limite não pode ser superior a 3 anos da data atual, tente novamente.', 'error');
                    } else if (reason === 'ultimoMembro') {
                        showNotification('Você não pode sair do grupo porque é a única pessoa do grupo.', 'error');
                    } else if (reason === 'camposInvalidos') {
                        showNotification('Os campos introduzidos estão inválidos, tente novamente.', 'error');
                    } else if (reason === 'erroeventoCriado') {
                        showNotification('Ocorreu um erro ao criar o evento, tente novamente mais tarde.', 'error');
                    }
                }

                // Remover parâmetros da URL após exibir a notificação
                removeQueryParams();

            }
        </script>
        <script src="assets/js/scriptd.js"></script>
    </body>

    </html>
<?php endif; ?>