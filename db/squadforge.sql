-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 14-Jun-2024 às 10:37
-- Versão do servidor: 10.4.24-MariaDB
-- versão do PHP: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `squadforge`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `chats`
--

CREATE TABLE `chats` (
  `idmensagem` int(11) NOT NULL,
  `conteudo` varchar(1000) DEFAULT NULL,
  `idutilizador` int(11) DEFAULT NULL,
  `datahora` datetime DEFAULT NULL,
  `codgrupo` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `eventos`
--

CREATE TABLE `eventos` (
  `idevento` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `cor` varchar(7) NOT NULL,
  `inicio` datetime NOT NULL,
  `fim` datetime NOT NULL,
  `codgrupo` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `grupos`
--

CREATE TABLE `grupos` (
  `codgrupo` varchar(10) NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `diretrizes` varchar(1000) DEFAULT NULL,
  `descricao` varchar(500) DEFAULT NULL,
  `idutilizadorDono` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `notificacoes`
--

CREATE TABLE `notificacoes` (
  `id` int(11) NOT NULL,
  `tipo_notificacao` varchar(50) DEFAULT NULL,
  `mensagem` text DEFAULT NULL,
  `idutilizador` int(11) DEFAULT NULL,
  `data` timestamp NOT NULL DEFAULT current_timestamp(),
  `lida` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `redefinirpp`
--

CREATE TABLE `redefinirpp` (
  `id` int(11) NOT NULL,
  `idutilizador` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expira_em` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tarefasg`
--

CREATE TABLE `tarefasg` (
  `idtarefa` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `datahora` datetime NOT NULL,
  `estado` enum('criada','em_progresso','terminada') NOT NULL DEFAULT 'criada',
  `codgrupo` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores`
--

CREATE TABLE `utilizadores` (
  `idutilizador` int(11) NOT NULL,
  `primeironome` varchar(255) DEFAULT NULL,
  `ultimonome` varchar(255) DEFAULT NULL,
  `nomeutilizador` varchar(255) DEFAULT NULL,
  `nomefuncao` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `palavrapasse` varchar(255) DEFAULT NULL,
  `fotoPath` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadorgrupo`
--

CREATE TABLE `utilizadorgrupo` (
  `idutilizador` int(11) NOT NULL,
  `codgrupo` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`idmensagem`),
  ADD KEY `idutilizador_ligc` (`idutilizador`),
  ADD KEY `codgrupo_ligc` (`codgrupo`);

--
-- Índices para tabela `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`idevento`),
  ADD KEY `codgrupo_lige` (`codgrupo`);

--
-- Índices para tabela `grupos`
--
ALTER TABLE `grupos`
  ADD PRIMARY KEY (`codgrupo`),
  ADD KEY `fk_idutilizadordono` (`idutilizadorDono`);

--
-- Índices para tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idutilizador` (`idutilizador`);

--
-- Índices para tabela `redefinirpp`
--
ALTER TABLE `redefinirpp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idutilizador` (`idutilizador`);

--
-- Índices para tabela `tarefasg`
--
ALTER TABLE `tarefasg`
  ADD PRIMARY KEY (`idtarefa`),
  ADD KEY `codgrupo` (`codgrupo`);

--
-- Índices para tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  ADD PRIMARY KEY (`idutilizador`);

--
-- Índices para tabela `utilizadorgrupo`
--
ALTER TABLE `utilizadorgrupo`
  ADD PRIMARY KEY (`idutilizador`,`codgrupo`),
  ADD KEY `codgrupo` (`codgrupo`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `chats`
--
ALTER TABLE `chats`
  MODIFY `idmensagem` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `idevento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `redefinirpp`
--
ALTER TABLE `redefinirpp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `tarefasg`
--
ALTER TABLE `tarefasg`
  MODIFY `idtarefa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  MODIFY `idutilizador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `codgrupo_ligc` FOREIGN KEY (`codgrupo`) REFERENCES `grupos` (`codgrupo`),
  ADD CONSTRAINT `idutilizador_ligc` FOREIGN KEY (`idutilizador`) REFERENCES `utilizadores` (`idutilizador`);

--
-- Limitadores para a tabela `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `fk_codgrupo` FOREIGN KEY (`codgrupo`) REFERENCES `grupos` (`codgrupo`);

--
-- Limitadores para a tabela `grupos`
--
ALTER TABLE `grupos`
  ADD CONSTRAINT `fk_idutilizadordono` FOREIGN KEY (`idutilizadorDono`) REFERENCES `utilizadores` (`idutilizador`);

--
-- Limitadores para a tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD CONSTRAINT `notificacoes_ibfk_1` FOREIGN KEY (`idutilizador`) REFERENCES `utilizadores` (`idutilizador`);

--
-- Limitadores para a tabela `redefinirpp`
--
ALTER TABLE `redefinirpp`
  ADD CONSTRAINT `redefinirpp_ibfk_1` FOREIGN KEY (`idutilizador`) REFERENCES `utilizadores` (`idutilizador`);

--
-- Limitadores para a tabela `tarefasg`
--
ALTER TABLE `tarefasg`
  ADD CONSTRAINT `tarefasg_ibfk_1` FOREIGN KEY (`codgrupo`) REFERENCES `grupos` (`codgrupo`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `utilizadorgrupo`
--
ALTER TABLE `utilizadorgrupo`
  ADD CONSTRAINT `utilizadorgrupo_ibfk_1` FOREIGN KEY (`idutilizador`) REFERENCES `utilizadores` (`idutilizador`),
  ADD CONSTRAINT `utilizadorgrupo_ibfk_2` FOREIGN KEY (`codgrupo`) REFERENCES `grupos` (`codgrupo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
