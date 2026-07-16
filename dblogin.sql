-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 17-Maio-2025 às 21:19
-- Versão do servidor: 5.7.31
-- versão do PHP: 7.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `dblogin`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `abastecimentos`
--

DROP TABLE IF EXISTS `abastecimentos`;
CREATE TABLE IF NOT EXISTS `abastecimentos` (
  `id_abast` int(100) NOT NULL AUTO_INCREMENT,
  `idfornecedor` int(20) DEFAULT NULL,
  `nfe` int(11) NOT NULL,
  `data_abast` date NOT NULL,
  `hora_abast` time NOT NULL,
  `idlink` int(100) NOT NULL,
  `chaveacesso` varchar(60) COLLATE utf8_bin NOT NULL,
  `idveiculo` int(5) NOT NULL,
  `idproduto` int(100) NOT NULL,
  `quantidade` float(10,3) DEFAULT NULL,
  `valor_unit` float NOT NULL,
  `km_abast` int(11) NOT NULL,
  `observacao` varchar(255) COLLATE utf8_bin NOT NULL,
  `valorcompra` decimal(10,2) GENERATED ALWAYS AS ((`quantidade` * `valor_unit`)) VIRTUAL,
  `mediaideal` float(10,2) DEFAULT '14.10',
  PRIMARY KEY (`id_abast`),
  KEY `idveiculo` (`idveiculo`),
  KEY `idveiculo_2` (`idveiculo`),
  KEY `idfornecedor` (`idfornecedor`),
  KEY `idproduto` (`idproduto`),
  KEY `idlink` (`idlink`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `ent`
-- (Veja abaixo para a view atual)
--
DROP VIEW IF EXISTS `ent`;
CREATE TABLE IF NOT EXISTS `ent` (
`dataconf` date
,`id_nota` int(11)
,`Idconta` int(10)
,`quant` int(255)
,`tipo` int(5)
,`nvalor` float(10,2)
,`saldo` double(19,2)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `entradas`
-- (Veja abaixo para a view atual)
--
DROP VIEW IF EXISTS `entradas`;
CREATE TABLE IF NOT EXISTS `entradas` (
`Idconta` int(10)
,`id_nota` int(11)
,`quant` int(255)
,`tipo` int(5)
,`nvalor` float(10,2)
);

-- --------------------------------------------------------

--
-- Estrutura da tabela `fornecedores`
--

DROP TABLE IF EXISTS `fornecedores`;
CREATE TABLE IF NOT EXISTS `fornecedores` (
  `idfornecedor` int(11) NOT NULL AUTO_INCREMENT,
  `razao_social` varchar(250) COLLATE utf8_bin NOT NULL,
  `endereco` varchar(150) COLLATE utf8_bin NOT NULL,
  `bairro` varchar(150) COLLATE utf8_bin NOT NULL,
  `cidade` varchar(100) COLLATE utf8_bin NOT NULL,
  `estado` varchar(100) COLLATE utf8_bin NOT NULL,
  `cep` varchar(8) COLLATE utf8_bin NOT NULL,
  `fone` varchar(100) COLLATE utf8_bin NOT NULL,
  `cnpj` varchar(14) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`idfornecedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='lista de fornecedores de conbustiveis';

-- --------------------------------------------------------

--
-- Estrutura da tabela `lc_cat`
--

DROP TABLE IF EXISTS `lc_cat`;
CREATE TABLE IF NOT EXISTS `lc_cat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `operacao` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `lc_conferencia`
--

DROP TABLE IF EXISTS `lc_conferencia`;
CREATE TABLE IF NOT EXISTS `lc_conferencia` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `dataconf` date DEFAULT NULL,
  `Idconta` int(10) NOT NULL,
  `id_nota` int(11) NOT NULL,
  `quant` int(255) NOT NULL,
  `tipo` int(5) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `id_nota` (`id_nota`),
  KEY `Idconta` (`Idconta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estrutura da tabela `lc_contass`
--

DROP TABLE IF EXISTS `lc_contass`;
CREATE TABLE IF NOT EXISTS `lc_contass` (
  `idconta` int(20) NOT NULL AUTO_INCREMENT,
  `conta` varchar(250) CHARACTER SET utf8 NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 NOT NULL,
  `proprietario` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`idconta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estrutura da tabela `lc_livros`
--

DROP TABLE IF EXISTS `lc_livros`;
CREATE TABLE IF NOT EXISTS `lc_livros` (
  `idlivro` int(3) NOT NULL AUTO_INCREMENT,
  `livro` varchar(100) COLLATE utf8_bin NOT NULL,
  UNIQUE KEY `id` (`idlivro`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estrutura da tabela `lc_movimento`
--

DROP TABLE IF EXISTS `lc_movimento`;
CREATE TABLE IF NOT EXISTS `lc_movimento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` int(11) DEFAULT NULL,
  `dia` int(2) NOT NULL,
  `mes` int(2) NOT NULL,
  `ano` int(4) NOT NULL,
  `idlivro` int(3) NOT NULL,
  `folha` int(5) NOT NULL,
  `cat` int(11) DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `valor` float(10,2) DEFAULT NULL,
  `datamov` date NOT NULL,
  `idconta` int(20) NOT NULL,
  `data_lancamento` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tipo` (`tipo`),
  KEY `idconta` (`idconta`),
  KEY `livro` (`idlivro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estrutura da tabela `lc_movimentos`
--

DROP TABLE IF EXISTS `lc_movimentos`;
CREATE TABLE IF NOT EXISTS `lc_movimentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` int(11) DEFAULT NULL,
  `livro` int(3) NOT NULL,
  `folha` int(5) NOT NULL,
  `cat` int(11) DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `valor` float DEFAULT NULL,
  `datamov` date NOT NULL,
  `idconta` int(20) NOT NULL,
  `data_lancamento` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tipo` (`tipo`),
  KEY `idconta` (`idconta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estrutura da tabela `lc_notas`
--

DROP TABLE IF EXISTS `lc_notas`;
CREATE TABLE IF NOT EXISTS `lc_notas` (
  `id_nota` int(11) NOT NULL AUTO_INCREMENT,
  `descnotas` varchar(100) COLLATE utf8_bin NOT NULL,
  `nvalor` float(10,2) NOT NULL,
  PRIMARY KEY (`id_nota`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estrutura da tabela `lc_recover`
--

DROP TABLE IF EXISTS `lc_recover`;
CREATE TABLE IF NOT EXISTS `lc_recover` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(200) COLLATE utf8_bin NOT NULL,
  `data_fim` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estrutura da tabela `linksnfe`
--

DROP TABLE IF EXISTS `linksnfe`;
CREATE TABLE IF NOT EXISTS `linksnfe` (
  `idlink` int(100) NOT NULL AUTO_INCREMENT,
  `link` varchar(250) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`idlink`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `mediakm`
-- (Veja abaixo para a view atual)
--
DROP VIEW IF EXISTS `mediakm`;
CREATE TABLE IF NOT EXISTS `mediakm` (
`idveiculo` int(5)
,`km_abast` int(11)
,`data_abast` date
,`hora_abast` time
,`quantidade` float(10,3)
,`valorcompra` decimal(10,2)
,`km_anterior` bigint(11)
,`km_perc` bigint(12)
,`kmporlitro` double(19,7)
,`reaisporkm` decimal(14,6)
,`litrosporkm` double(14,7)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `mediakm2`
-- (Veja abaixo para a view atual)
--
DROP VIEW IF EXISTS `mediakm2`;
CREATE TABLE IF NOT EXISTS `mediakm2` (
`idveiculo` int(5)
,`km_abast` int(11)
,`data_abast` date
,`hora_abast` time
,`quantidade` float(10,3)
,`valorcompra` decimal(10,2)
,`km_anterior` bigint(11)
,`km_perc` bigint(12)
,`kmporlitro` double(19,7)
,`reaisporkm` decimal(14,6)
,`litrosporkm` double(14,7)
);

-- --------------------------------------------------------

--
-- Estrutura da tabela `produtos`
--

DROP TABLE IF EXISTS `produtos`;
CREATE TABLE IF NOT EXISTS `produtos` (
  `idprod` int(100) NOT NULL AUTO_INCREMENT,
  `codigo` int(100) NOT NULL,
  `produto` varchar(100) COLLATE utf8_bin NOT NULL,
  `unid` varchar(5) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`idprod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `sai`
-- (Veja abaixo para a view atual)
--
DROP VIEW IF EXISTS `sai`;
CREATE TABLE IF NOT EXISTS `sai` (
`datav` date
,`id_nota` int(11)
,`Idconta` int(10)
,`quant` int(255)
,`tipo` int(5)
,`nvalor` float(10,2)
,`saldo` double(19,2)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `saldo_c`
-- (Veja abaixo para a view atual)
--
DROP VIEW IF EXISTS `saldo_c`;
CREATE TABLE IF NOT EXISTS `saldo_c` (
`SUM(ent.saldo)` double(19,2)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `saldo_d`
-- (Veja abaixo para a view atual)
--
DROP VIEW IF EXISTS `saldo_d`;
CREATE TABLE IF NOT EXISTS `saldo_d` (
`SUM(sai.saldo)` double(19,2)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `soma`
-- (Veja abaixo para a view atual)
--
DROP VIEW IF EXISTS `soma`;
CREATE TABLE IF NOT EXISTS `soma` (
`Idconta` int(10)
,`id_nota` int(11)
,`quant` int(255)
,`tipo` int(5)
,`nvalor` float(10,2)
,`saldo` double(19,2)
);

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `user_email` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `user_pass` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `user_nomemae` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `user_pergunta` varchar(100) DEFAULT NULL,
  `user_resposta` varchar(100) DEFAULT NULL,
  `user_nasc` date DEFAULT NULL,
  `joining_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `users_foto` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `veiculos`
--

DROP TABLE IF EXISTS `veiculos`;
CREATE TABLE IF NOT EXISTS `veiculos` (
  `idveiculo` int(50) NOT NULL AUTO_INCREMENT,
  `placa` varchar(7) COLLATE utf8_bin NOT NULL,
  `marca` varchar(100) COLLATE utf8_bin NOT NULL,
  `modelo` varchar(100) COLLATE utf8_bin NOT NULL,
  `anofab` int(4) NOT NULL,
  `anomod` int(4) NOT NULL,
  `renavan` int(20) DEFAULT NULL,
  `chassi` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`idveiculo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estrutura para vista `ent`
--
DROP TABLE IF EXISTS `ent`;

DROP VIEW IF EXISTS `ent`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `ent`  AS  select `lc_conferencia`.`dataconf` AS `dataconf`,`lc_conferencia`.`id_nota` AS `id_nota`,`lc_conferencia`.`Idconta` AS `Idconta`,`lc_conferencia`.`quant` AS `quant`,`lc_conferencia`.`tipo` AS `tipo`,`lc_notas`.`nvalor` AS `nvalor`,(`lc_conferencia`.`quant` * `lc_notas`.`nvalor`) AS `saldo` from (`lc_conferencia` join `lc_notas` on((`lc_conferencia`.`id_nota` = `lc_notas`.`id_nota`))) where ((`lc_conferencia`.`Idconta` = 9) and (`lc_conferencia`.`tipo` = 1)) ;

-- --------------------------------------------------------

--
-- Estrutura para vista `entradas`
--
DROP TABLE IF EXISTS `entradas`;

DROP VIEW IF EXISTS `entradas`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `entradas`  AS  select `lc_conferencia`.`Idconta` AS `Idconta`,`lc_conferencia`.`id_nota` AS `id_nota`,`lc_conferencia`.`quant` AS `quant`,`lc_conferencia`.`tipo` AS `tipo`,`lc_notas`.`nvalor` AS `nvalor` from (`lc_conferencia` join `lc_notas` on((`lc_conferencia`.`id_nota` = `lc_notas`.`id_nota`))) ;

-- --------------------------------------------------------

--
-- Estrutura para vista `mediakm`
--
DROP TABLE IF EXISTS `mediakm`;

DROP VIEW IF EXISTS `mediakm`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `mediakm`  AS  select `abastecimentos`.`idveiculo` AS `idveiculo`,`abastecimentos`.`km_abast` AS `km_abast`,`abastecimentos`.`data_abast` AS `data_abast`,`abastecimentos`.`hora_abast` AS `hora_abast`,`abastecimentos`.`quantidade` AS `quantidade`,`abastecimentos`.`valorcompra` AS `valorcompra`,(select max(`l1`.`km_abast`) from `abastecimentos` `l1` where ((`l1`.`idveiculo` = `abastecimentos`.`idveiculo`) and (`l1`.`id_abast` < `abastecimentos`.`id_abast`))) AS `km_anterior`,(select (`abastecimentos`.`km_abast` - `km_anterior`)) AS `km_perc`,(select (`km_perc` / `abastecimentos`.`quantidade`)) AS `kmporlitro`,(select (`abastecimentos`.`valorcompra` / `km_perc`)) AS `reaisporkm`,(select (`abastecimentos`.`quantidade` / `km_perc`)) AS `litrosporkm` from `abastecimentos` where (`abastecimentos`.`idveiculo` = 1) ;

-- --------------------------------------------------------

--
-- Estrutura para vista `mediakm2`
--
DROP TABLE IF EXISTS `mediakm2`;

DROP VIEW IF EXISTS `mediakm2`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `mediakm2`  AS  select `abastecimentos`.`idveiculo` AS `idveiculo`,`abastecimentos`.`km_abast` AS `km_abast`,`abastecimentos`.`data_abast` AS `data_abast`,`abastecimentos`.`hora_abast` AS `hora_abast`,`abastecimentos`.`quantidade` AS `quantidade`,`abastecimentos`.`valorcompra` AS `valorcompra`,(select max(`l1`.`km_abast`) from `abastecimentos` `l1` where ((`l1`.`idveiculo` = `abastecimentos`.`idveiculo`) and (`l1`.`id_abast` < `abastecimentos`.`id_abast`))) AS `km_anterior`,(select (`abastecimentos`.`km_abast` - `km_anterior`)) AS `km_perc`,(select (`km_perc` / `abastecimentos`.`quantidade`)) AS `kmporlitro`,(select (`abastecimentos`.`valorcompra` / `km_perc`)) AS `reaisporkm`,(select (`abastecimentos`.`quantidade` / `km_perc`)) AS `litrosporkm` from `abastecimentos` ;

-- --------------------------------------------------------

--
-- Estrutura para vista `sai`
--
DROP TABLE IF EXISTS `sai`;

DROP VIEW IF EXISTS `sai`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `sai`  AS  select `lc_conferencia`.`dataconf` AS `datav`,`lc_conferencia`.`id_nota` AS `id_nota`,`lc_conferencia`.`Idconta` AS `Idconta`,`lc_conferencia`.`quant` AS `quant`,`lc_conferencia`.`tipo` AS `tipo`,`lc_notas`.`nvalor` AS `nvalor`,(`lc_conferencia`.`quant` * `lc_notas`.`nvalor`) AS `saldo` from (`lc_conferencia` join `lc_notas` on((`lc_conferencia`.`id_nota` = `lc_notas`.`id_nota`))) where ((`lc_conferencia`.`Idconta` = 9) and (`lc_conferencia`.`tipo` = 0)) ;

-- --------------------------------------------------------

--
-- Estrutura para vista `saldo_c`
--
DROP TABLE IF EXISTS `saldo_c`;

DROP VIEW IF EXISTS `saldo_c`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `saldo_c`  AS  select sum(`ent`.`saldo`) AS `SUM(ent.saldo)` from `ent` where (`ent`.`Idconta` = 9) ;

-- --------------------------------------------------------

--
-- Estrutura para vista `saldo_d`
--
DROP TABLE IF EXISTS `saldo_d`;

DROP VIEW IF EXISTS `saldo_d`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `saldo_d`  AS  select sum(`sai`.`saldo`) AS `SUM(sai.saldo)` from `sai` where (`sai`.`Idconta` = 9) ;

-- --------------------------------------------------------

--
-- Estrutura para vista `soma`
--
DROP TABLE IF EXISTS `soma`;

DROP VIEW IF EXISTS `soma`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `soma`  AS  select `lc_conferencia`.`Idconta` AS `Idconta`,`lc_conferencia`.`id_nota` AS `id_nota`,`lc_conferencia`.`quant` AS `quant`,`lc_conferencia`.`tipo` AS `tipo`,`lc_notas`.`nvalor` AS `nvalor`,(`lc_conferencia`.`quant` * `lc_notas`.`nvalor`) AS `saldo` from (`lc_conferencia` join `lc_notas` on((`lc_conferencia`.`id_nota` = `lc_notas`.`id_nota`))) where ((`lc_conferencia`.`Idconta` = 9) and (`lc_conferencia`.`tipo` = 1)) ;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `abastecimentos`
--
ALTER TABLE `abastecimentos`
  ADD CONSTRAINT `idfornecedor` FOREIGN KEY (`idfornecedor`) REFERENCES `fornecedores` (`idfornecedor`),
  ADD CONSTRAINT `idlink` FOREIGN KEY (`idlink`) REFERENCES `linksnfe` (`idlink`),
  ADD CONSTRAINT `idproduto` FOREIGN KEY (`idproduto`) REFERENCES `produtos` (`idprod`),
  ADD CONSTRAINT `idveiculo` FOREIGN KEY (`idveiculo`) REFERENCES `veiculos` (`idveiculo`);

--
-- Limitadores para a tabela `lc_conferencia`
--
ALTER TABLE `lc_conferencia`
  ADD CONSTRAINT `lc_conferencia_ibfk_1` FOREIGN KEY (`id_nota`) REFERENCES `lc_notas` (`id_nota`),
  ADD CONSTRAINT `lc_conferencia_ibfk_2` FOREIGN KEY (`Idconta`) REFERENCES `lc_contass` (`idconta`);

--
-- Limitadores para a tabela `lc_movimento`
--
ALTER TABLE `lc_movimento`
  ADD CONSTRAINT `lc_movimento` FOREIGN KEY (`idconta`) REFERENCES `lc_contass` (`idconta`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
