/*
 Navicat MySQL Data Transfer

 Source Server         : local
 Source Server Type    : MySQL
 Source Server Version : 80040
 Source Host           : 127.0.0.1:3306
 Source Schema         : local_kamut_craft

 Target Server Type    : MySQL
 Target Server Version : 80040
 File Encoding         : 65001

 Date: 15/09/2025 09:38:50
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for addresses
-- ----------------------------
DROP TABLE IF EXISTS `addresses`;
CREATE TABLE `addresses` (
  `id` int NOT NULL,
  `primaryOwnerId` int DEFAULT NULL,
  `fieldId` int DEFAULT NULL,
  `countryCode` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `administrativeArea` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `locality` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dependentLocality` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `postalCode` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sortingCode` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `addressLine1` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `addressLine2` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `addressLine3` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `organization` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `organizationTaxId` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `fullName` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `firstName` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `lastName` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `latitude` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `longitude` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ywvvjuanafvaumlwleczfutpuccjvifiensf` (`primaryOwnerId`),
  CONSTRAINT `fk_wwllmxcmsfokcmbwdvugikmutmrcwwynbbxe` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ywvvjuanafvaumlwleczfutpuccjvifiensf` FOREIGN KEY (`primaryOwnerId`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for announcements
-- ----------------------------
DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `pluginId` int DEFAULT NULL,
  `heading` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `unread` tinyint(1) NOT NULL DEFAULT '1',
  `dateRead` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_biquutbicgkcqfrarbusaqumjbdaisqqsgcf` (`userId`,`unread`,`dateRead`,`dateCreated`),
  KEY `idx_jzrtbazwhsctrqwvzgetlrpwkitspkymlxyk` (`dateRead`),
  KEY `fk_foownajjjfedgrxnattxswmiwthjmemuvlqn` (`pluginId`),
  CONSTRAINT `fk_duheaaefrlytvwmpqtnawtewzocrscabdmqv` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_foownajjjfedgrxnattxswmiwthjmemuvlqn` FOREIGN KEY (`pluginId`) REFERENCES `plugins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for assetindexdata
-- ----------------------------
DROP TABLE IF EXISTS `assetindexdata`;
CREATE TABLE `assetindexdata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sessionId` int NOT NULL,
  `volumeId` int NOT NULL,
  `uri` text COLLATE utf8mb3_unicode_ci,
  `size` bigint unsigned DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `isDir` tinyint(1) DEFAULT '0',
  `recordId` int DEFAULT NULL,
  `isSkipped` tinyint(1) DEFAULT '0',
  `inProgress` tinyint(1) DEFAULT '0',
  `completed` tinyint(1) DEFAULT '0',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_potdqxuwqgtkfulfosstiirihpjuggzsicvz` (`volumeId`),
  KEY `idx_mglehuibayuihtlolaqvjlmbpndfkelmjvjd` (`volumeId`),
  KEY `fk_kwdbsiayjqbmsurbpboycskoaludhqfhjich` (`sessionId`),
  CONSTRAINT `fk_cuigxpohnpbacgmmsmjsofjaadeksljniapu` FOREIGN KEY (`volumeId`) REFERENCES `volumes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kwdbsiayjqbmsurbpboycskoaludhqfhjich` FOREIGN KEY (`sessionId`) REFERENCES `assetindexingsessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for assetindexingsessions
-- ----------------------------
DROP TABLE IF EXISTS `assetindexingsessions`;
CREATE TABLE `assetindexingsessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `indexedVolumes` text COLLATE utf8mb3_unicode_ci,
  `totalEntries` int DEFAULT NULL,
  `processedEntries` int NOT NULL DEFAULT '0',
  `cacheRemoteImages` tinyint(1) DEFAULT NULL,
  `listEmptyFolders` tinyint(1) DEFAULT '0',
  `isCli` tinyint(1) DEFAULT '0',
  `actionRequired` tinyint(1) DEFAULT '0',
  `processIfRootEmpty` tinyint(1) DEFAULT '0',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for assets
-- ----------------------------
DROP TABLE IF EXISTS `assets`;
CREATE TABLE `assets` (
  `id` int NOT NULL,
  `volumeId` int DEFAULT NULL,
  `folderId` int NOT NULL,
  `uploaderId` int DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `mimeType` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `kind` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'unknown',
  `alt` text COLLATE utf8mb3_unicode_ci,
  `width` int unsigned DEFAULT NULL,
  `height` int unsigned DEFAULT NULL,
  `size` bigint unsigned DEFAULT NULL,
  `focalPoint` varchar(13) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `deletedWithVolume` tinyint(1) DEFAULT NULL,
  `keptFile` tinyint(1) DEFAULT NULL,
  `dateModified` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ykybkwwornnuqhduertqnlnfhyndzcmswqlb` (`filename`,`folderId`),
  KEY `idx_ltwesbedetufvwlchpujfoalxptlwkrluijy` (`folderId`),
  KEY `idx_ybzpkawbxvmrizjqszbkglbkgrmbkoygcgkh` (`volumeId`),
  KEY `fk_wdiyvnxybongksyswdgbsuamyikpbtpoxhmt` (`uploaderId`),
  CONSTRAINT `fk_meolibhdkhnplpartyoxvedzgcctxyouiwgd` FOREIGN KEY (`folderId`) REFERENCES `volumefolders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_obvogmnekbyuxxssybldqjkmjoewmslucabw` FOREIGN KEY (`volumeId`) REFERENCES `volumes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wdiyvnxybongksyswdgbsuamyikpbtpoxhmt` FOREIGN KEY (`uploaderId`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ybnlovtdpyvrbfpijbgpbmswnbxtfhiavejt` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for assets_sites
-- ----------------------------
DROP TABLE IF EXISTS `assets_sites`;
CREATE TABLE `assets_sites` (
  `assetId` int NOT NULL,
  `siteId` int NOT NULL,
  `alt` text COLLATE utf8mb3_unicode_ci,
  PRIMARY KEY (`assetId`,`siteId`),
  KEY `fk_zexcjxybisudauovfhwnjpfvigwemaywntcb` (`siteId`),
  CONSTRAINT `fk_ybjprbbubxzrdccchutaeczdsyzghxnsonrb` FOREIGN KEY (`assetId`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_zexcjxybisudauovfhwnjpfvigwemaywntcb` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for assettransformindex
-- ----------------------------
DROP TABLE IF EXISTS `assettransformindex`;
CREATE TABLE `assettransformindex` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assetId` int NOT NULL,
  `filename` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `format` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `volumeId` int DEFAULT NULL,
  `fileExists` tinyint(1) NOT NULL DEFAULT '0',
  `inProgress` tinyint(1) NOT NULL DEFAULT '0',
  `error` tinyint(1) NOT NULL DEFAULT '0',
  `dateIndexed` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_nvehxqzceltxxyudoluvxfcogpfuapusnntj` (`volumeId`,`assetId`,`location`),
  KEY `idx_ttpdrzfsrswzmbnscbrplllqqcnbfawnwbnu` (`assetId`,`format`,`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for assettransforms
-- ----------------------------
DROP TABLE IF EXISTS `assettransforms`;
CREATE TABLE `assettransforms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `mode` enum('stretch','fit','crop') COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'crop',
  `position` enum('top-left','top-center','top-right','center-left','center-center','center-right','bottom-left','bottom-center','bottom-right') COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'center-center',
  `width` int unsigned DEFAULT NULL,
  `height` int unsigned DEFAULT NULL,
  `format` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `quality` int DEFAULT NULL,
  `interlace` enum('none','line','plane','partition') COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'none',
  `dimensionChangeTime` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_lwphkpzyrpqycyypohuvvexlznrbropokdfe` (`name`),
  KEY `idx_jnqbrifzvmepkcaokfogimvunwszlhqomphb` (`handle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for authenticator
-- ----------------------------
DROP TABLE IF EXISTS `authenticator`;
CREATE TABLE `authenticator` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `auth2faSecret` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `oldTimestamp` int unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ncaezerddzmzmqjcaqkzbbcvxmwkkkzrzsxz` (`userId`),
  CONSTRAINT `fk_ncaezerddzmzmqjcaqkzbbcvxmwkkkzrzsxz` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for blitz_caches
-- ----------------------------
DROP TABLE IF EXISTS `blitz_caches`;
CREATE TABLE `blitz_caches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `siteId` int NOT NULL,
  `uri` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `paginate` int DEFAULT NULL,
  `expiryDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_amxkzhbugrkjbmcrkxbgmyhodwtghqrewbit` (`siteId`,`uri`),
  KEY `idx_mkehoszdfzzrdgtzrwwxqvmumadteiuddxjx` (`expiryDate`),
  CONSTRAINT `fk_cikellpumruzrsovfanluzjmqouzrcaovwys` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for blitz_cachetags
-- ----------------------------
DROP TABLE IF EXISTS `blitz_cachetags`;
CREATE TABLE `blitz_cachetags` (
  `cacheId` int NOT NULL,
  `tag` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`cacheId`,`tag`),
  KEY `idx_jprmvaqlsmbpgxukigxeokblmlpisyqrprms` (`tag`),
  CONSTRAINT `fk_ecvnctxrrslohaqbhxqlzezqwxnkwzyreufz` FOREIGN KEY (`cacheId`) REFERENCES `blitz_caches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for blitz_driverdata
-- ----------------------------
DROP TABLE IF EXISTS `blitz_driverdata`;
CREATE TABLE `blitz_driverdata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `driver` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `data` text COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for blitz_elementcaches
-- ----------------------------
DROP TABLE IF EXISTS `blitz_elementcaches`;
CREATE TABLE `blitz_elementcaches` (
  `cacheId` int NOT NULL,
  `elementId` int NOT NULL,
  PRIMARY KEY (`cacheId`,`elementId`),
  KEY `fk_uerlbbgxqdukkylyfmxkjgygwdqctsntnbvr` (`elementId`),
  CONSTRAINT `fk_fqpoqabgqonzbtljbopklayddnbseyzpzyhd` FOREIGN KEY (`cacheId`) REFERENCES `blitz_caches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_uerlbbgxqdukkylyfmxkjgygwdqctsntnbvr` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for blitz_elementexpirydates
-- ----------------------------
DROP TABLE IF EXISTS `blitz_elementexpirydates`;
CREATE TABLE `blitz_elementexpirydates` (
  `elementId` int NOT NULL,
  `expiryDate` datetime DEFAULT NULL,
  PRIMARY KEY (`elementId`),
  UNIQUE KEY `idx_fgtbznckbhpcbpdmwiiqumykhnerzxcxxzwe` (`elementId`),
  KEY `idx_tqxpdggiclnomnhdfftdufbduquxquhiseff` (`expiryDate`),
  CONSTRAINT `fk_erhppqnjuzulqcpekbysbjujgkzjhcrngmmq` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for blitz_elementqueries
-- ----------------------------
DROP TABLE IF EXISTS `blitz_elementqueries`;
CREATE TABLE `blitz_elementqueries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `index` bigint NOT NULL,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `params` text COLLATE utf8mb3_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_jyxqdokgnlubdciuomjcfnmlzbiuaadcejel` (`index`),
  KEY `idx_iczybpqxzlsxjuszkdsvgchydivipfxrlkqw` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for blitz_elementquerycaches
-- ----------------------------
DROP TABLE IF EXISTS `blitz_elementquerycaches`;
CREATE TABLE `blitz_elementquerycaches` (
  `cacheId` int NOT NULL,
  `queryId` int NOT NULL,
  PRIMARY KEY (`cacheId`,`queryId`),
  UNIQUE KEY `idx_ptmshtbuynhegjwqxjruovctcuckspdskbnb` (`cacheId`,`queryId`),
  KEY `fk_bzvdmdbwdvikqwlyqzkzcumrqelffmzuvnbr` (`queryId`),
  CONSTRAINT `fk_bzvdmdbwdvikqwlyqzkzcumrqelffmzuvnbr` FOREIGN KEY (`queryId`) REFERENCES `blitz_elementqueries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rivinnhqpvazwodlwmhrdahdhplziyxurmfd` FOREIGN KEY (`cacheId`) REFERENCES `blitz_caches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for blitz_elementquerysources
-- ----------------------------
DROP TABLE IF EXISTS `blitz_elementquerysources`;
CREATE TABLE `blitz_elementquerysources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sourceId` int DEFAULT NULL,
  `queryId` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_qstxibjypiyufmqmclfmhrbwvdhieftvrwef` (`sourceId`,`queryId`),
  KEY `fk_nepfskdlslpcisnrpntnkfhdtcqqndidwrhx` (`queryId`),
  CONSTRAINT `fk_nepfskdlslpcisnrpntnkfhdtcqqndidwrhx` FOREIGN KEY (`queryId`) REFERENCES `blitz_elementqueries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=437 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for bulkopevents
-- ----------------------------
DROP TABLE IF EXISTS `bulkopevents`;
CREATE TABLE `bulkopevents` (
  `key` char(10) COLLATE utf8mb3_unicode_ci NOT NULL,
  `senderClass` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `eventName` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`key`,`senderClass`,`eventName`),
  KEY `idx_srhpoknrnjzxxygvjdlaiaoztzdnueqotson` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for categories
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int NOT NULL,
  `groupId` int NOT NULL,
  `parentId` int DEFAULT NULL,
  `deletedWithGroup` tinyint(1) DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_aleagpetcgqupppowiuhdddxvugavypcnejr` (`groupId`),
  KEY `fk_psgfoasasxquligekbivxqleyvuiroclmawm` (`parentId`),
  CONSTRAINT `fk_idxjergufjcuifnlklwygonwvjdjjpexiwez` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_psgfoasasxquligekbivxqleyvuiroclmawm` FOREIGN KEY (`parentId`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ywgrujcfscggmovdvfpcnxdramskdgukcjgg` FOREIGN KEY (`groupId`) REFERENCES `categorygroups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for categorygroups
-- ----------------------------
DROP TABLE IF EXISTS `categorygroups`;
CREATE TABLE `categorygroups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `structureId` int NOT NULL,
  `fieldLayoutId` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `defaultPlacement` enum('beginning','end') COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'end',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `dateDeleted` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_dhsyryfyqteojngdcgjiebfohctwnwpkllnh` (`name`),
  KEY `idx_sefkkdkeehqhxytahjlxntpnporakfsbzmiu` (`handle`),
  KEY `idx_vazawxuiftbtgdqksqitxcdsygcxvesdlvcl` (`structureId`),
  KEY `idx_ghylvffhnrpiercuxuedgrzneuvwckcgdxhj` (`fieldLayoutId`),
  KEY `idx_octerpdsirfiglixbbdflrjkucquqykudfcl` (`dateDeleted`),
  CONSTRAINT `fk_fzrrffynmgzjrwzxwetamxhvburxixmcknyo` FOREIGN KEY (`structureId`) REFERENCES `structures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vweahqubeagshvhvxqzqzzabdcrondqwrfxw` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for categorygroups_sites
-- ----------------------------
DROP TABLE IF EXISTS `categorygroups_sites`;
CREATE TABLE `categorygroups_sites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `groupId` int NOT NULL,
  `siteId` int NOT NULL,
  `hasUrls` tinyint(1) NOT NULL DEFAULT '1',
  `uriFormat` text COLLATE utf8mb3_unicode_ci,
  `template` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_tibxspzzhwyontocicdbhpdgzopftdfforbs` (`groupId`,`siteId`),
  KEY `idx_bzmswfhbghqexiqfoplafpstxrogbrhmybtr` (`siteId`),
  CONSTRAINT `fk_iwwxjpifrrpkmtolwuskmrwzoftfaramjrgx` FOREIGN KEY (`groupId`) REFERENCES `categorygroups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rqzcywtrbrawbtlbiqycgfzukgwmpbzxcezq` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for changedattributes
-- ----------------------------
DROP TABLE IF EXISTS `changedattributes`;
CREATE TABLE `changedattributes` (
  `elementId` int NOT NULL,
  `siteId` int NOT NULL,
  `attribute` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `propagated` tinyint(1) NOT NULL,
  `userId` int DEFAULT NULL,
  PRIMARY KEY (`elementId`,`siteId`,`attribute`),
  KEY `idx_bsssmmnsavctmqfmuobvaxoogqdxbtpzsrxy` (`elementId`,`siteId`,`dateUpdated`),
  KEY `fk_urmbnohgwwrmzkctvmvwagxwusdtspkkpgnm` (`siteId`),
  KEY `fk_iacxaihhlzhklzncesyoszqjacmlcusdsegy` (`userId`),
  CONSTRAINT `fk_iacxaihhlzhklzncesyoszqjacmlcusdsegy` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_kqbdprmhcqecqmcngaezyuphdtlojcsgxqqo` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_urmbnohgwwrmzkctvmvwagxwusdtspkkpgnm` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for changedfields
-- ----------------------------
DROP TABLE IF EXISTS `changedfields`;
CREATE TABLE `changedfields` (
  `elementId` int NOT NULL,
  `siteId` int NOT NULL,
  `fieldId` int NOT NULL,
  `layoutElementUid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  `dateUpdated` datetime NOT NULL,
  `propagated` tinyint(1) NOT NULL,
  `userId` int DEFAULT NULL,
  PRIMARY KEY (`elementId`,`siteId`,`fieldId`,`layoutElementUid`),
  KEY `idx_goatdahjqtgwmzublvopuxndmqggstjvhsfw` (`elementId`,`siteId`,`dateUpdated`),
  KEY `fk_hqeltouusrbxhfeoejgvgictxhexssgkfbkj` (`siteId`),
  KEY `fk_islmrtmbnrkurmtwnjhubnrsntrascjbgwdt` (`fieldId`),
  KEY `fk_kjccciowwaqtrhynulschlbxohniihfvbvws` (`userId`),
  CONSTRAINT `fk_hqeltouusrbxhfeoejgvgictxhexssgkfbkj` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_islmrtmbnrkurmtwnjhubnrsntrascjbgwdt` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_jmgjdwaaaveigglxliceqfvfvkbwemwcyxih` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_kjccciowwaqtrhynulschlbxohniihfvbvws` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for cloudflare_rules
-- ----------------------------
DROP TABLE IF EXISTS `cloudflare_rules`;
CREATE TABLE `cloudflare_rules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `siteId` int NOT NULL,
  `trigger` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `urlsToClear` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `refresh` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_kywnspryzmngozsorqwdrtaitdrtqdxctviu` (`siteId`),
  CONSTRAINT `fk_kywnspryzmngozsorqwdrtaitdrtqdxctviu` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for content
-- ----------------------------
DROP TABLE IF EXISTS `content`;
CREATE TABLE `content` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elementId` int NOT NULL,
  `siteId` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  `field_facebookPageUrl_reryjhmp` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `field_instagramPageUrl_fyraetku` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `field_youtubeChannelUrl_skwzojja` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `field_titleField_dlqprxpz` text COLLATE utf8mb3_unicode_ci,
  `field_body_xcnsewhf` text COLLATE utf8mb3_unicode_ci,
  `field_boxColor_gupagijm` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `field_hideDecorations_xwcnnqhr` tinyint(1) DEFAULT NULL,
  `field_addToThePageSummary_psuaysxh` tinyint(1) DEFAULT NULL,
  `field_table_mrxntpoe` text COLLATE utf8mb3_unicode_ci,
  `field_videoUrl_qncboqqk` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `field_enableImageModal_sxacopui` tinyint(1) DEFAULT NULL,
  `field_hideStoreFinderBlock_gvtkjuyn` tinyint(1) DEFAULT NULL,
  `field_hideContactBlock_itptelef` tinyint(1) DEFAULT NULL,
  `field_seoMetadescription_rkaqdznv` varchar(640) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `field_seo_moauisec` text COLLATE utf8mb3_unicode_ci,
  `field_findProductsCopy_iwvixsjv` text COLLATE utf8mb3_unicode_ci,
  `field_contactUsCopy_ctaeckyl` text COLLATE utf8mb3_unicode_ci,
  `field_seoTitle_zbkqrqzt` text COLLATE utf8mb3_unicode_ci,
  `field_diamondBoxTitle_poelofhc` text COLLATE utf8mb3_unicode_ci,
  `field_recipeShortDescription_eudqtrvj` text COLLATE utf8mb3_unicode_ci,
  `field_recipeIngredientsList_efrhxtgc` text COLLATE utf8mb3_unicode_ci,
  `field_recipeProcedure_gdesyiet` text COLLATE utf8mb3_unicode_ci,
  `field_recipeDifficulty_lvxsqfdx` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `field_recipeKcal_klczaiqo` text COLLATE utf8mb3_unicode_ci,
  `field_recipeTime_jtzrydcp` text COLLATE utf8mb3_unicode_ci,
  `field_hideFooterMenu_drzaudkw` tinyint(1) DEFAULT NULL,
  `field_submenuAlwaysOpen_rsqjyczd` tinyint(1) DEFAULT NULL,
  `field_removeTopPadding_eceeyhju` tinyint(1) DEFAULT NULL,
  `field_removeBottomPadding_cyzlbmod` tinyint(1) DEFAULT NULL,
  `field_emailAddress_rnnbomjw` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `field_cookiePolicyLabel_rbmtqujq` text COLLATE utf8mb3_unicode_ci,
  `field_privacyPolicyLabel_mqrbakjz` text COLLATE utf8mb3_unicode_ci,
  `field_legalFooterCopy_rhyrzstm` text COLLATE utf8mb3_unicode_ci,
  `field_t_recipesProcedure_uiitwxbz` text COLLATE utf8mb3_unicode_ci,
  `field_t_readMore_imkmmhfy` text COLLATE utf8mb3_unicode_ci,
  `field_t_recipesDifficulty_hzklvcdx` text COLLATE utf8mb3_unicode_ci,
  `field_t_recipesNoRecipesFound_nojzqgmt` text COLLATE utf8mb3_unicode_ci,
  `field_t_recipesAvailableCategories_zeorgobo` text COLLATE utf8mb3_unicode_ci,
  `field_t_contactUs_mhfkzpgb` text COLLATE utf8mb3_unicode_ci,
  `field_t_recipes_ttrqnkud` text COLLATE utf8mb3_unicode_ci,
  `field_t_summary_dwhbsvfo` text COLLATE utf8mb3_unicode_ci,
  `field_t_search_swqkrfox` text COLLATE utf8mb3_unicode_ci,
  `field_t_recipesFound_skjpvmqu` text COLLATE utf8mb3_unicode_ci,
  `field_t_recipesKcal_dcwwlrhi` text COLLATE utf8mb3_unicode_ci,
  `field_t_recipesRelated_tzdgxbzs` text COLLATE utf8mb3_unicode_ci,
  `field_t_recipesIngredients_wkailypq` text COLLATE utf8mb3_unicode_ci,
  `field_t_findProducts_fhsyzrqh` text COLLATE utf8mb3_unicode_ci,
  `field_t_image_swbzgirh` text COLLATE utf8mb3_unicode_ci,
  `field_t_for_adxhwtrd` text COLLATE utf8mb3_unicode_ci,
  `field_t_recipesSearch_nahsktbr` text COLLATE utf8mb3_unicode_ci,
  `field_t_selectOne_ohtdnkbu` text COLLATE utf8mb3_unicode_ci,
  `field_t_recipesTime_rkrgsjsx` text COLLATE utf8mb3_unicode_ci,
  `field_t_recipesFilterByType_jocfasqf` text COLLATE utf8mb3_unicode_ci,
  `field_t_switchLanguage_vseuuacx` text COLLATE utf8mb3_unicode_ci,
  `field_t_formFirstName_xlxxzpdm` text COLLATE utf8mb3_unicode_ci,
  `field_contactPerson_pbnejxly` text COLLATE utf8mb3_unicode_ci,
  `field_countryCode_aqiywwla` text COLLATE utf8mb3_unicode_ci,
  `field_t_formPhone_xhhqeirc` text COLLATE utf8mb3_unicode_ci,
  `field_t_formSubmit_mnkxvikv` text COLLATE utf8mb3_unicode_ci,
  `field_t_formSubject_jrofdzgp` text COLLATE utf8mb3_unicode_ci,
  `field_region_kxmcyrjd` text COLLATE utf8mb3_unicode_ci,
  `field_t_formNewsletter_kpozlquv` text COLLATE utf8mb3_unicode_ci,
  `field_form_wnuyiyma` int DEFAULT NULL,
  `field_t_formEmail_dwdlgmyo` text COLLATE utf8mb3_unicode_ci,
  `field_t_formLastName_fxevmogg` text COLLATE utf8mb3_unicode_ci,
  `field_t_formMessage_xwccxjoc` text COLLATE utf8mb3_unicode_ci,
  `field_t_formErrorMessage_wxlhlsmq` text COLLATE utf8mb3_unicode_ci,
  `field_t_formSuccessMessage_ukajjvgo` text COLLATE utf8mb3_unicode_ci,
  `field_t_formCommunications_hqnmqkro` text COLLATE utf8mb3_unicode_ci,
  `field_t_formCountry_zamlziaf` text COLLATE utf8mb3_unicode_ci,
  `field_t_recipesDifficultyDifficult_wmeacqvp` text COLLATE utf8mb3_unicode_ci,
  `field_t_recipesDifficultyAverage_tmkoddux` text COLLATE utf8mb3_unicode_ci,
  `field_t_recipesDifficultyEasy_falnxtle` text COLLATE utf8mb3_unicode_ci,
  `field_textAlignment_dgcupiuj` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `field_additionalInfo_qbjuvqro` text COLLATE utf8mb3_unicode_ci,
  `field_shortLink_wzwbfcge` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `field_teamMemberShortLink_mwbkqwnp` text COLLATE utf8mb3_unicode_ci,
  `field_forceDownload_vusedsnp` tinyint(1) DEFAULT NULL,
  `field_issuuDocumentName_nafnveiz` text COLLATE utf8mb3_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_afncklspscgrbbixdjldnqymoaoksesbiqmk` (`elementId`,`siteId`),
  KEY `idx_dgsqnsoqpggkcjigitlvehnxxvyghzilgfue` (`siteId`),
  KEY `idx_csghixwshbrddzwkcssqxwdckdlznbuvgumt` (`title`),
  CONSTRAINT `fk_bgcqzpoxnwqsflrlwdhqrdidaqbobbhnxlrf` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_wwqmgbzjtjuruupoyhxajrzbsaliyimgsicw` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=155728 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for contentblocks
-- ----------------------------
DROP TABLE IF EXISTS `contentblocks`;
CREATE TABLE `contentblocks` (
  `id` int NOT NULL,
  `primaryOwnerId` int DEFAULT NULL,
  `fieldId` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_enbrbvwipjoywjequuwukyyeetbzlrcisymk` (`primaryOwnerId`),
  KEY `idx_mabkyofsihvskzexkugczeybkyybxvikwrym` (`fieldId`),
  CONSTRAINT `fk_bjctidkcqcbxlouxnlcmmpxhmapjfvyweaxj` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cqpihcvneabnwvzhhnekwebhfyphgnxnyzct` FOREIGN KEY (`primaryOwnerId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jkwtmvvnrggiawsseabudzrghzpcdcjghlwm` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for cookie_consent_consent
-- ----------------------------
DROP TABLE IF EXISTS `cookie_consent_consent`;
CREATE TABLE `cookie_consent_consent` (
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  `site_id` int DEFAULT NULL,
  `ip` varchar(39) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `data` text COLLATE utf8mb3_unicode_ci,
  `cookieName` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT 'cookie-consent',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `fk_cookie_consent_consent_belong_to_site` (`site_id`),
  CONSTRAINT `fk_cookie_consent_consent_belong_to_site` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for cookie_consent_group
-- ----------------------------
DROP TABLE IF EXISTS `cookie_consent_group`;
CREATE TABLE `cookie_consent_group` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `required` tinyint(1) NOT NULL,
  `store_ip` tinyint(1) NOT NULL,
  `default` tinyint(1) NOT NULL,
  `cookies` text COLLATE utf8mb3_unicode_ci,
  `description` text COLLATE utf8mb3_unicode_ci,
  `site_id` int DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `order` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cookie_consent_group_belong_to_site` (`site_id`),
  CONSTRAINT `fk_cookie_consent_group_belong_to_site` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for cookie_consent_site_settings
-- ----------------------------
DROP TABLE IF EXISTS `cookie_consent_site_settings`;
CREATE TABLE `cookie_consent_site_settings` (
  `site_id` int NOT NULL AUTO_INCREMENT,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  `activated` tinyint(1) NOT NULL DEFAULT '0',
  `cssAssets` tinyint(1) NOT NULL DEFAULT '1',
  `jsAssets` tinyint(1) NOT NULL DEFAULT '1',
  `templateAsset` tinyint(1) NOT NULL DEFAULT '1',
  `showCheckboxes` tinyint(1) NOT NULL DEFAULT '1',
  `showAfterConsent` tinyint(1) NOT NULL DEFAULT '1',
  `cookieName` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'cookie-consent',
  `acceptAllButton` tinyint(1) NOT NULL DEFAULT '0',
  `headline` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb3_unicode_ci,
  `template` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `refresh` tinyint(1) NOT NULL DEFAULT '0',
  `refresh_time` int NOT NULL DEFAULT '500',
  `dateInvalidated` datetime NOT NULL DEFAULT '2019-05-14 00:00:00',
  PRIMARY KEY (`site_id`),
  CONSTRAINT `fk_cookie_consent_setting_belong_to_site` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for craftidtokens
-- ----------------------------
DROP TABLE IF EXISTS `craftidtokens`;
CREATE TABLE `craftidtokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `accessToken` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `expiryDate` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_nggqxmjimlzvfqtamcfefxjhebgpadyplsxv` (`userId`),
  CONSTRAINT `fk_nggqxmjimlzvfqtamcfefxjhebgpadyplsxv` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for deprecationerrors
-- ----------------------------
DROP TABLE IF EXISTS `deprecationerrors`;
CREATE TABLE `deprecationerrors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `fingerprint` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `lastOccurrence` datetime NOT NULL,
  `file` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `line` smallint unsigned DEFAULT NULL,
  `message` text COLLATE utf8mb3_unicode_ci,
  `traces` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_qvlzugofxbzvhosngyizhuewrhabglonaery` (`key`,`fingerprint`),
  CONSTRAINT `deprecationerrors_chk_1` CHECK (json_valid(`traces`))
) ENGINE=InnoDB AUTO_INCREMENT=13086 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for drafts
-- ----------------------------
DROP TABLE IF EXISTS `drafts`;
CREATE TABLE `drafts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `canonicalId` int DEFAULT NULL,
  `creatorId` int DEFAULT NULL,
  `provisional` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb3_unicode_ci,
  `trackChanges` tinyint(1) NOT NULL DEFAULT '0',
  `dateLastMerged` datetime DEFAULT NULL,
  `saved` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_laiglohzwwtnkcydyegcubfexvhdyenovcfr` (`creatorId`,`provisional`),
  KEY `idx_speendkvmrovqaqfliuvgxfonxzxnyjqrkto` (`saved`),
  KEY `fk_dafgmslhofmjesstdnrljguubuayltcrbmjs` (`canonicalId`),
  CONSTRAINT `fk_dafgmslhofmjesstdnrljguubuayltcrbmjs` FOREIGN KEY (`canonicalId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tzhbcigzpzcxexuqwmjbklpdkwghzkrqsgsb` FOREIGN KEY (`creatorId`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1078 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for elementactivity
-- ----------------------------
DROP TABLE IF EXISTS `elementactivity`;
CREATE TABLE `elementactivity` (
  `elementId` int NOT NULL,
  `userId` int NOT NULL,
  `siteId` int NOT NULL,
  `draftId` int DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`elementId`,`userId`,`type`),
  KEY `idx_pjdwxinmirfnpkmqchsnoyrtfavrgknisjur` (`elementId`,`timestamp`,`userId`),
  KEY `fk_fjufztqusnmqclygnvmhstgeqjasqztwcrng` (`userId`),
  KEY `fk_ymizwjteywturofbrvswepgukxpvopqfodni` (`siteId`),
  KEY `fk_ngcustujzgajrmpzfolcrkufwmqbscwxzuxz` (`draftId`),
  CONSTRAINT `fk_fjufztqusnmqclygnvmhstgeqjasqztwcrng` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fmrxwsmbadyzybeplhdlfqexhonaoayvmmcg` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ngcustujzgajrmpzfolcrkufwmqbscwxzuxz` FOREIGN KEY (`draftId`) REFERENCES `drafts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ymizwjteywturofbrvswepgukxpvopqfodni` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for elementindexsettings
-- ----------------------------
DROP TABLE IF EXISTS `elementindexsettings`;
CREATE TABLE `elementindexsettings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `settings` text COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_ekageeeaeaoqehregplmfixuctmpkhbscjka` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for elements
-- ----------------------------
DROP TABLE IF EXISTS `elements`;
CREATE TABLE `elements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `canonicalId` int DEFAULT NULL,
  `draftId` int DEFAULT NULL,
  `revisionId` int DEFAULT NULL,
  `fieldLayoutId` int DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `archived` tinyint(1) NOT NULL DEFAULT '0',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `dateLastMerged` datetime DEFAULT NULL,
  `dateDeleted` datetime DEFAULT NULL,
  `deletedWithOwner` tinyint(1) DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_zushioejkzjwlvddgkdnudbduamqclzqboks` (`dateDeleted`),
  KEY `idx_rljttoyslgrjlkzivzwlgyekqbrykfxzebud` (`fieldLayoutId`),
  KEY `idx_fevrvvypsqpiyldudyhffnwodihiytrnkqnp` (`type`),
  KEY `idx_zhjirgxvinhfxanvliiceesgafhgyfrnrwbj` (`enabled`),
  KEY `idx_hudqrrfdbncynwwhxpoblvwrkgpflcbuzzcl` (`archived`,`dateCreated`),
  KEY `idx_yurqnhjqvbbkkqbfclxejiwuixzyewvfansm` (`archived`,`dateDeleted`,`draftId`,`revisionId`,`canonicalId`),
  KEY `fk_hbzemdsptxmowerujpcidwnzabgisyouqyit` (`canonicalId`),
  KEY `fk_uhrxikjcwsnsmkhiwjeiyzhfvtshitbwfisx` (`draftId`),
  KEY `fk_hjcnoolkxbkdrujbunwyytwarnmeudwumrnc` (`revisionId`),
  KEY `idx_weqksaknlqjijxiprchmkkuckgdlnkhciesc` (`archived`,`dateDeleted`,`draftId`,`revisionId`,`canonicalId`,`enabled`),
  CONSTRAINT `fk_enmpolmtenfrjckbsmlwjkbsveijibanneir` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hbzemdsptxmowerujpcidwnzabgisyouqyit` FOREIGN KEY (`canonicalId`) REFERENCES `elements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hjcnoolkxbkdrujbunwyytwarnmeudwumrnc` FOREIGN KEY (`revisionId`) REFERENCES `revisions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_uhrxikjcwsnsmkhiwjeiyzhfvtshitbwfisx` FOREIGN KEY (`draftId`) REFERENCES `drafts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27712 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for elements_bulkops
-- ----------------------------
DROP TABLE IF EXISTS `elements_bulkops`;
CREATE TABLE `elements_bulkops` (
  `elementId` int NOT NULL,
  `key` char(10) COLLATE utf8mb3_unicode_ci NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`elementId`,`key`),
  KEY `idx_nlqaqwguuosygconmbkxpvbzmlnmimcgbewe` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for elements_owners
-- ----------------------------
DROP TABLE IF EXISTS `elements_owners`;
CREATE TABLE `elements_owners` (
  `elementId` int NOT NULL,
  `ownerId` int NOT NULL,
  `sortOrder` smallint unsigned NOT NULL,
  PRIMARY KEY (`elementId`,`ownerId`),
  KEY `fk_xaherqspprvgbydlefecnoqcortabinjjqfc` (`ownerId`),
  CONSTRAINT `fk_axbahpwckaikxjhwbzwlaowpjxbdxlszkfmf` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_xaherqspprvgbydlefecnoqcortabinjjqfc` FOREIGN KEY (`ownerId`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for elements_sites
-- ----------------------------
DROP TABLE IF EXISTS `elements_sites`;
CREATE TABLE `elements_sites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elementId` int NOT NULL,
  `siteId` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `uri` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_qbsnlvsqjuivbkozdqwwrlrixkhmpmzewtlz` (`elementId`,`siteId`),
  KEY `idx_lhqzcsmonfofdihodzxjyeaoyadyfyetymdb` (`siteId`),
  KEY `idx_udydkwtnlrjdzucgqrxenensfhsvhppcused` (`slug`,`siteId`),
  KEY `idx_gmcrlccyqjktfiftzlhrqremkoahtzyohoei` (`enabled`),
  KEY `idx_irumomemcknvowsywfrknwaawtzsteaniphi` (`uri`,`siteId`),
  KEY `idx_ebuyjjbsohroyhgojivyzcuzhvdzgfkywsgt` (`title`,`siteId`),
  CONSTRAINT `fk_dwkvriumndzfabglasufbqxvdecyuokaxuql` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fgbqaacjclcmlhdmwgwrdiqfveehftrdqrln` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `elements_sites_chk_1` CHECK (json_valid(`content`))
) ENGINE=InnoDB AUTO_INCREMENT=157371 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for entries
-- ----------------------------
DROP TABLE IF EXISTS `entries`;
CREATE TABLE `entries` (
  `id` int NOT NULL,
  `sectionId` int DEFAULT NULL,
  `parentId` int DEFAULT NULL,
  `primaryOwnerId` int DEFAULT NULL,
  `fieldId` int DEFAULT NULL,
  `typeId` int NOT NULL,
  `postDate` datetime DEFAULT NULL,
  `expiryDate` datetime DEFAULT NULL,
  `status` enum('live','pending','expired') COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'live',
  `deletedWithEntryType` tinyint(1) DEFAULT NULL,
  `deletedWithSection` tinyint(1) DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vxbnoruxessytfbssqcqoezjxtmlasjwttvd` (`postDate`),
  KEY `idx_ycmnteuycdtmlrvucqobyoxdgxqbikzfvmvt` (`expiryDate`),
  KEY `idx_gseiffxfyfelbbnmwgzulukyhmnonyeuswbf` (`sectionId`),
  KEY `idx_nhlgulqwoghvirkykugovvpvxjjnfmfueoyc` (`typeId`),
  KEY `fk_jstcmtutkiogmxxsfmzepdupuncbybadnkxb` (`parentId`),
  KEY `idx_zuquuqgwdnhcdqmzosceetnigkvmpgaerkzn` (`primaryOwnerId`),
  KEY `idx_gmyepsoobnliatkiwqsqnuzhcqkbuhljfhdz` (`fieldId`),
  KEY `idx_taapftmawvnlhstowrkfnkugocbqrkdpapiz` (`status`),
  CONSTRAINT `fk_dowktubpzbbtuwsfhnqtpchczlniikaaarsb` FOREIGN KEY (`sectionId`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fyhjkzhirxbtxcewmiirlhzrhwsszefzjsga` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hxohtbicggtqrriarfgyrzxoyhoyvjskarof` FOREIGN KEY (`primaryOwnerId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jstcmtutkiogmxxsfmzepdupuncbybadnkxb` FOREIGN KEY (`parentId`) REFERENCES `entries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_nqtxcfqbutzxwchnrmsgxdtyanqifjvteful` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ttqkjswkdpntmgybyejmgbpacxjtwmqwyteu` FOREIGN KEY (`typeId`) REFERENCES `entrytypes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for entries_authors
-- ----------------------------
DROP TABLE IF EXISTS `entries_authors`;
CREATE TABLE `entries_authors` (
  `entryId` int NOT NULL,
  `authorId` int NOT NULL,
  `sortOrder` smallint unsigned NOT NULL,
  PRIMARY KEY (`entryId`,`authorId`),
  KEY `idx_tzrwnjkprvlvskenqpkwyjdhejzpmuqzvsvw` (`authorId`),
  KEY `idx_whnfdolsyxaqbanjjbsgpxbwuiavzzecsgtf` (`entryId`,`sortOrder`),
  CONSTRAINT `fk_mxichdrcrbxebwpghnznzxbqrvzaqieqenai` FOREIGN KEY (`authorId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pfqomyuamqmuadjsjtnbfvlefbiemepulhcl` FOREIGN KEY (`entryId`) REFERENCES `entries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for entrytypes
-- ----------------------------
DROP TABLE IF EXISTS `entrytypes`;
CREATE TABLE `entrytypes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fieldLayoutId` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb3_unicode_ci,
  `icon` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `color` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `hasTitleField` tinyint(1) NOT NULL DEFAULT '1',
  `titleTranslationMethod` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'site',
  `titleTranslationKeyFormat` text COLLATE utf8mb3_unicode_ci,
  `titleFormat` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `showSlugField` tinyint(1) DEFAULT '1',
  `slugTranslationMethod` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'site',
  `slugTranslationKeyFormat` text COLLATE utf8mb3_unicode_ci,
  `showStatusField` tinyint(1) DEFAULT '1',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `dateDeleted` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_ccqlehhimlpgegfxkbmeupehqqbebqqjgkrh` (`fieldLayoutId`),
  KEY `idx_vtwjqytzdzaaijvabjvoaeqnpfkjhitojhoj` (`dateDeleted`),
  CONSTRAINT `fk_sldobjkwospkireaafhccdshhqxqlmlgznnu` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for fieldgroups
-- ----------------------------
DROP TABLE IF EXISTS `fieldgroups`;
CREATE TABLE `fieldgroups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `dateDeleted` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_bgavkoqrpwxflzkzaivcfetotuzbwpkpjost` (`name`),
  KEY `idx_gngdaahaqlslnfrevatqkmewnknbihyuqlfk` (`dateDeleted`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for fieldlayoutfields
-- ----------------------------
DROP TABLE IF EXISTS `fieldlayoutfields`;
CREATE TABLE `fieldlayoutfields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `layoutId` int NOT NULL,
  `tabId` int NOT NULL,
  `fieldId` int NOT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `sortOrder` smallint unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_sagfadeivszseoreayvkoctadknoffzqvesh` (`layoutId`,`fieldId`),
  KEY `idx_ddhfltqojatlyrnmbdxsxgnyrikzijdwzdgh` (`sortOrder`),
  KEY `idx_kbelekbknupjwwwovpbknbrlgzlxezbhaxub` (`tabId`),
  KEY `idx_vluuwanqqfejydoxtpbknhgewoefwzybktwe` (`fieldId`),
  CONSTRAINT `fk_efqxueaapihukcxutzalelpcjfnukbfmgvpg` FOREIGN KEY (`layoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_gfxepplzjgyxksosvygjcpzvvxiyanbbcqcl` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_seyfjycurilucegrktwxqyibdheugmuzbxfa` FOREIGN KEY (`tabId`) REFERENCES `fieldlayouttabs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1986 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for fieldlayouts
-- ----------------------------
DROP TABLE IF EXISTS `fieldlayouts`;
CREATE TABLE `fieldlayouts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `dateDeleted` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_gournismdsuozyrinetdqysknjtjibxpedjh` (`dateDeleted`),
  KEY `idx_vusvdusdizrovowlpjkeoejzqmpiadnjzotx` (`type`),
  CONSTRAINT `fieldlayouts_chk_1` CHECK (json_valid(`config`))
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for fieldlayouttabs
-- ----------------------------
DROP TABLE IF EXISTS `fieldlayouttabs`;
CREATE TABLE `fieldlayouttabs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `layoutId` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `elements` text COLLATE utf8mb3_unicode_ci,
  `sortOrder` smallint unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_novvpzleisiorbngiqekilfzbvqsjplodvwh` (`sortOrder`),
  KEY `idx_rgsfdnwgcjewfjuuidibwyomlxbwxenzttin` (`layoutId`),
  CONSTRAINT `fk_jeyludpjntdbnnjhadcelrdqacqypumbrrqp` FOREIGN KEY (`layoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=710 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for fields
-- ----------------------------
DROP TABLE IF EXISTS `fields`;
CREATE TABLE `fields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(64) COLLATE utf8mb3_unicode_ci NOT NULL,
  `context` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'global',
  `columnSuffix` char(8) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `instructions` text COLLATE utf8mb3_unicode_ci,
  `searchable` tinyint(1) NOT NULL DEFAULT '1',
  `translationMethod` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'none',
  `translationKeyFormat` text COLLATE utf8mb3_unicode_ci,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `settings` text COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `dateDeleted` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_entfqguumjdwpqisyxnqsxlbiopgsrtwldbp` (`handle`,`context`),
  KEY `idx_odrcmtfxugpzcetxxjebmrpmrnabdtxmkfai` (`context`),
  KEY `idx_zbidikzvgaxarqsfssvyhbsudkmmkhoburbg` (`dateDeleted`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_crm_fields
-- ----------------------------
DROP TABLE IF EXISTS `freeform_crm_fields`;
CREATE TABLE `freeform_crm_fields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `integrationId` int NOT NULL,
  `label` text COLLATE utf8mb3_unicode_ci,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL,
  `required` tinyint(1) DEFAULT '0',
  `options` longtext COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  `category` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `freeform_crm_fields_type_idx` (`type`),
  KEY `freeform_crm_fields_integrationId_fk` (`integrationId`),
  KEY `idx_qlpocohkamslkwgamyuvsqoqxqrjvomwwzrg` (`integrationId`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_email_marketing_fields
-- ----------------------------
DROP TABLE IF EXISTS `freeform_email_marketing_fields`;
CREATE TABLE `freeform_email_marketing_fields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mailingListId` int NOT NULL,
  `label` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL,
  `required` tinyint(1) DEFAULT '0',
  `options` longtext COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  `category` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `freeform_mailing_list_fields_type_idx` (`type`),
  KEY `freeform_mailing_list_fields_mailingListId_fk` (`mailingListId`),
  KEY `idx_heubmbeepcgtgyhjweuhspluvqhozlsshwqz` (`mailingListId`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_email_marketing_lists
-- ----------------------------
DROP TABLE IF EXISTS `freeform_email_marketing_lists`;
CREATE TABLE `freeform_email_marketing_lists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `integrationId` int NOT NULL,
  `resourceId` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `memberCount` int DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `freeform_mailing_lists_integrationId_resourceId_unq_idx` (`integrationId`,`resourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_export_notifications
-- ----------------------------
DROP TABLE IF EXISTS `freeform_export_notifications`;
CREATE TABLE `freeform_export_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `profileId` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `fileType` varchar(30) NOT NULL,
  `fileName` varchar(255) DEFAULT NULL,
  `frequency` varchar(20) NOT NULL,
  `recipients` longtext,
  `subject` varchar(255) DEFAULT NULL,
  `message` text,
  `dateCreated` datetime DEFAULT NULL,
  `dateUpdated` datetime DEFAULT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `fk_mpiwbsqctrxgreuchhbfykprfawlipthmlon` (`profileId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Table structure for freeform_export_profiles
-- ----------------------------
DROP TABLE IF EXISTS `freeform_export_profiles`;
CREATE TABLE `freeform_export_profiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `formId` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `limit` int DEFAULT NULL,
  `dateRange` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `rangeStart` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `rangeEnd` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `fields` longtext COLLATE utf8mb3_unicode_ci,
  `filters` longtext COLLATE utf8mb3_unicode_ci,
  `statuses` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `freeform_export_profiles_formId_fk` (`formId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_export_settings
-- ----------------------------
DROP TABLE IF EXISTS `freeform_export_settings`;
CREATE TABLE `freeform_export_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `setting` longtext COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `freeform_export_settings_userId_fk` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_favorite_fields
-- ----------------------------
DROP TABLE IF EXISTS `freeform_favorite_fields`;
CREATE TABLE `freeform_favorite_fields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `metadata` longtext COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_xspbdetwhbogibhifevqkknbssdnnzrqlkuy` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_feed_messages
-- ----------------------------
DROP TABLE IF EXISTS `freeform_feed_messages`;
CREATE TABLE `freeform_feed_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `feedId` int NOT NULL,
  `message` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `conditions` longtext COLLATE utf8mb3_unicode_ci,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `seen` tinyint(1) NOT NULL DEFAULT '0',
  `issueDate` datetime NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `freeform_feed_messages_feedId_fk` (`feedId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_feeds
-- ----------------------------
DROP TABLE IF EXISTS `freeform_feeds`;
CREATE TABLE `freeform_feeds` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hash` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `min` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `max` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `issueDate` datetime NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `freeform_feeds_hash_unq_idx` (`hash`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_fields
-- ----------------------------
DROP TABLE IF EXISTS `freeform_fields`;
CREATE TABLE `freeform_fields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `required` tinyint(1) DEFAULT '0',
  `instructions` text COLLATE utf8mb3_unicode_ci,
  `metaProperties` text COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `handle` (`handle`),
  KEY `freeform_fields_type_idx` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_fields_type_groups
-- ----------------------------
DROP TABLE IF EXISTS `freeform_fields_type_groups`;
CREATE TABLE `freeform_fields_type_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `color` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `types` longtext COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime DEFAULT NULL,
  `dateUpdated` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_forms
-- ----------------------------
DROP TABLE IF EXISTS `freeform_forms`;
CREATE TABLE `freeform_forms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `spamBlockCount` int unsigned NOT NULL DEFAULT '0',
  `metadata` longtext COLLATE utf8mb3_unicode_ci,
  `order` int DEFAULT NULL,
  `createdByUserId` int DEFAULT NULL,
  `gtmId` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `gtmEventName` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `updatedByUserId` int DEFAULT NULL,
  `dateUpdated` datetime NOT NULL,
  `dateArchived` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `handle` (`handle`),
  KEY `idx_hitmyovctzhxyqxkxtjkstjrpfloustonbcv` (`order`),
  KEY `freeform_forms_createdByUserId_fk` (`createdByUserId`),
  KEY `freeform_forms_updatedByUserId_fk` (`updatedByUserId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_forms_fields
-- ----------------------------
DROP TABLE IF EXISTS `freeform_forms_fields`;
CREATE TABLE `freeform_forms_fields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `formId` int NOT NULL,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `metadata` longtext COLLATE utf8mb3_unicode_ci,
  `rowId` int DEFAULT NULL,
  `order` int DEFAULT '0',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_ouovxjstglpkzvnnhabpmywzhdmvmaujiwsn` (`rowId`,`order`),
  KEY `fk_kuyeobqycyrtvlkwbjpfsermswrseqvvinxd` (`formId`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_forms_groups
-- ----------------------------
DROP TABLE IF EXISTS `freeform_forms_groups`;
CREATE TABLE `freeform_forms_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `siteId` int NOT NULL,
  `label` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `order` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_sbikhrbskzorlsgmhiemydfgrfwqejqowagp` (`siteId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_forms_groups_entries
-- ----------------------------
DROP TABLE IF EXISTS `freeform_forms_groups_entries`;
CREATE TABLE `freeform_forms_groups_entries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `groupId` int NOT NULL,
  `formId` int NOT NULL,
  `order` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_hezxyppjhdmgiyrimqukprumnwqoyakdpczh` (`groupId`),
  KEY `fk_bihrvkkraviwiqjykmhllemsyitfeoiqlpil` (`formId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_forms_integrations
-- ----------------------------
DROP TABLE IF EXISTS `freeform_forms_integrations`;
CREATE TABLE `freeform_forms_integrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `integrationId` int NOT NULL,
  `formId` int NOT NULL,
  `enabled` tinyint(1) DEFAULT '1',
  `metadata` longtext COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_dzfyykjomkqyxuljpxrnkiuirxqkuuzrjwxh` (`integrationId`),
  KEY `fk_zdylezthdkxebfpqrpmilwlaomhwdkhysucp` (`formId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_forms_layouts
-- ----------------------------
DROP TABLE IF EXISTS `freeform_forms_layouts`;
CREATE TABLE `freeform_forms_layouts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `formId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_gkbvrmkshtiqmscfoyjmdrdjmtuyzliquuag` (`formId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_forms_notifications
-- ----------------------------
DROP TABLE IF EXISTS `freeform_forms_notifications`;
CREATE TABLE `freeform_forms_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `formId` int NOT NULL,
  `class` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `enabled` tinyint(1) DEFAULT '1',
  `metadata` longtext COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_wtmvonuntwukiajmrrswlykcziglaxjvzzkh` (`formId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_forms_pages
-- ----------------------------
DROP TABLE IF EXISTS `freeform_forms_pages`;
CREATE TABLE `freeform_forms_pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `formId` int NOT NULL,
  `layoutId` int NOT NULL,
  `label` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `order` int DEFAULT '0',
  `metadata` longtext COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_itpaljlbifdnmgmgswanfozjnrbmczimxjrj` (`formId`,`order`),
  KEY `fk_ylaikudyknlmzjsmfqrmsphsylzfnqyjpydg` (`layoutId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_forms_rows
-- ----------------------------
DROP TABLE IF EXISTS `freeform_forms_rows`;
CREATE TABLE `freeform_forms_rows` (
  `id` int NOT NULL AUTO_INCREMENT,
  `formId` int NOT NULL,
  `layoutId` int NOT NULL,
  `order` int DEFAULT '0',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_qnidiieyxchifnryzkcdfajgoproaiuoditk` (`formId`,`order`),
  KEY `fk_sekaryzidytkylsjzwxzfnaomdocakfetlpr` (`layoutId`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_forms_sites
-- ----------------------------
DROP TABLE IF EXISTS `freeform_forms_sites`;
CREATE TABLE `freeform_forms_sites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `formId` int NOT NULL,
  `siteId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_ddajcxbdsbsypxpelyffeeqaynqwpseyjrlj` (`siteId`,`formId`),
  KEY `fk_fxctugwfwgtzasuijcparzgchnhjirfsazjd` (`formId`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_forms_translations
-- ----------------------------
DROP TABLE IF EXISTS `freeform_forms_translations`;
CREATE TABLE `freeform_forms_translations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `formId` int NOT NULL,
  `siteId` int NOT NULL,
  `translations` longtext COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_rlqpxlswikckwztnhotudbckidwpdojjvvkx` (`formId`,`siteId`),
  KEY `fk_zszchuhrhjvzljvztenbgcgrqicyutpgigkf` (`siteId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_integrations
-- ----------------------------
DROP TABLE IF EXISTS `freeform_integrations`;
CREATE TABLE `freeform_integrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `enabled` tinyint(1) DEFAULT '1',
  `legacy` tinyint(1) DEFAULT '0',
  `connectionEstablished` tinyint(1) DEFAULT '0',
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `metadata` longtext COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `handle` (`handle`),
  KEY `freeform_integrations_type_idx` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_integrations_queue
-- ----------------------------
DROP TABLE IF EXISTS `freeform_integrations_queue`;
CREATE TABLE `freeform_integrations_queue` (
  `id` int NOT NULL AUTO_INCREMENT,
  `submissionId` int NOT NULL,
  `integrationType` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL,
  `fieldHash` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `freeform_integrations_queue_status_idx` (`status`),
  KEY `freeform_integrations_queue_submissionId_fk` (`submissionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_limited_users
-- ----------------------------
DROP TABLE IF EXISTS `freeform_limited_users`;
CREATE TABLE `freeform_limited_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `settings` longtext COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_lock
-- ----------------------------
DROP TABLE IF EXISTS `freeform_lock`;
CREATE TABLE `freeform_lock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `freeform_lock_key_dateCreated_idx` (`key`,`dateCreated`),
  KEY `freeform_lock_dateCreated_idx` (`dateCreated`)
) ENGINE=InnoDB AUTO_INCREMENT=22063 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_mailing_list_fields
-- ----------------------------
DROP TABLE IF EXISTS `freeform_mailing_list_fields`;
CREATE TABLE `freeform_mailing_list_fields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mailingListId` int NOT NULL,
  `label` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL,
  `required` tinyint(1) DEFAULT '0',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `freeform_mailing_list_fields_type_idx` (`type`),
  KEY `freeform_mailing_list_fields_mailingListId_fk` (`mailingListId`),
  CONSTRAINT `freeform_mailing_list_fields_mailingListId_fk` FOREIGN KEY (`mailingListId`) REFERENCES `freeform_mailing_lists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_mailing_lists
-- ----------------------------
DROP TABLE IF EXISTS `freeform_mailing_lists`;
CREATE TABLE `freeform_mailing_lists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `integrationId` int NOT NULL,
  `resourceId` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `memberCount` int DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `freeform_mailing_lists_integrationId_resourceId_unq_idx` (`integrationId`,`resourceId`),
  CONSTRAINT `freeform_mailing_lists_integrationId_fk` FOREIGN KEY (`integrationId`) REFERENCES `freeform_integrations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_notification_log
-- ----------------------------
DROP TABLE IF EXISTS `freeform_notification_log`;
CREATE TABLE `freeform_notification_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(30) COLLATE utf8mb3_unicode_ci NOT NULL,
  `identifier` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `digestDate` date NOT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_type_digestDate_identifier` (`type`,`digestDate`,`identifier`),
  KEY `freeform_notification_log_type_dateCreated_idx` (`type`,`dateCreated`),
  KEY `idx_type_identifier_name_digestDate` (`type`,`identifier`,`name`,`digestDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_notification_template_wrappers
-- ----------------------------
DROP TABLE IF EXISTS `freeform_notification_template_wrappers`;
CREATE TABLE `freeform_notification_template_wrappers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb3_unicode_ci,
  `content` longtext COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime DEFAULT NULL,
  `dateUpdated` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `handle` (`handle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_notification_templates
-- ----------------------------
DROP TABLE IF EXISTS `freeform_notification_templates`;
CREATE TABLE `freeform_notification_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `formId` int DEFAULT NULL,
  `wrapperId` int DEFAULT NULL,
  `pdfTemplateIds` text COLLATE utf8mb3_unicode_ci,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb3_unicode_ci,
  `fromName` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `fromEmail` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `replyToName` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `replyToEmail` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cc` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bcc` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bodyHtml` mediumtext COLLATE utf8mb3_unicode_ci,
  `bodyText` mediumtext COLLATE utf8mb3_unicode_ci,
  `autoText` tinyint(1) NOT NULL DEFAULT '1',
  `includeAttachments` tinyint(1) DEFAULT '1',
  `presetAssets` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sortOrder` int DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_wrapperId` (`wrapperId`),
  KEY `freeform_notification_templates_formId` (`formId`),
  CONSTRAINT `fk_kfhkunttvcscpyxqtsjclxrhufmydftfeitd` FOREIGN KEY (`formId`) REFERENCES `freeform_forms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wrapperId` FOREIGN KEY (`wrapperId`) REFERENCES `freeform_notification_template_wrappers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_notifications
-- ----------------------------
DROP TABLE IF EXISTS `freeform_notifications`;
CREATE TABLE `freeform_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb3_unicode_ci,
  `fromName` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `fromEmail` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `replyToName` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `replyToEmail` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cc` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bcc` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bodyHtml` text COLLATE utf8mb3_unicode_ci,
  `bodyText` text COLLATE utf8mb3_unicode_ci,
  `autoText` tinyint(1) NOT NULL DEFAULT '1',
  `includeAttachments` tinyint(1) DEFAULT '1',
  `presetAssets` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sortOrder` int DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `handle` (`handle`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_payment_gateway_fields
-- ----------------------------
DROP TABLE IF EXISTS `freeform_payment_gateway_fields`;
CREATE TABLE `freeform_payment_gateway_fields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `integrationId` int NOT NULL,
  `label` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL,
  `required` tinyint(1) DEFAULT '0',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `freeform_payment_gateway_fields_type_idx` (`type`),
  KEY `freeform_payment_gateway_fields_integrationId_fk` (`integrationId`),
  CONSTRAINT `freeform_payment_gateway_fields_integrationId_fk` FOREIGN KEY (`integrationId`) REFERENCES `freeform_integrations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_payments
-- ----------------------------
DROP TABLE IF EXISTS `freeform_payments`;
CREATE TABLE `freeform_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `integrationId` int NOT NULL,
  `fieldId` int NOT NULL,
  `submissionId` int NOT NULL,
  `resourceId` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `type` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `status` varchar(40) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `metadata` longtext COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime DEFAULT NULL,
  `dateUpdated` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_bjjjakftjcuhegdecpywcijpknetoghfgsup` (`integrationId`,`resourceId`),
  KEY `fk_aqxwraqpekeyoxnmavaezrcwlfoqyhyzwygv` (`submissionId`),
  KEY `fk_mwtcyhrifntwlafgdbkcwuatojkfphkopfwd` (`fieldId`),
  KEY `idx_grygsckpvvwosqskitztmxrxdwhetdvdunbg` (`integrationId`,`type`),
  KEY `idx_hjmzaxshwgvcgordzgscoupnpnegswyakisv` (`resourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_payments_payments
-- ----------------------------
DROP TABLE IF EXISTS `freeform_payments_payments`;
CREATE TABLE `freeform_payments_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `integrationId` int NOT NULL,
  `submissionId` int NOT NULL,
  `subscriptionId` int DEFAULT NULL,
  `resourceId` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `last4` smallint DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `metadata` mediumtext COLLATE utf8mb3_unicode_ci,
  `errorCode` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `errorMessage` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `freeform_payments_payments_integrationId_resourceId_unq_idx` (`integrationId`,`resourceId`),
  KEY `freeform_payments_payments_submissionId_fk` (`submissionId`),
  KEY `freeform_payments_payments_subscriptionId_fk` (`subscriptionId`),
  CONSTRAINT `freeform_payments_payments_integrationId_fk` FOREIGN KEY (`integrationId`) REFERENCES `freeform_integrations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `freeform_payments_payments_submissionId_fk` FOREIGN KEY (`submissionId`) REFERENCES `freeform_submissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `freeform_payments_payments_subscriptionId_fk` FOREIGN KEY (`subscriptionId`) REFERENCES `freeform_payments_subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_payments_subscription_plans
-- ----------------------------
DROP TABLE IF EXISTS `freeform_payments_subscription_plans`;
CREATE TABLE `freeform_payments_subscription_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `integrationId` int NOT NULL,
  `resourceId` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `freeform_payments_subscription_plans_integrationId_fk` (`integrationId`),
  CONSTRAINT `freeform_payments_subscription_plans_integrationId_fk` FOREIGN KEY (`integrationId`) REFERENCES `freeform_integrations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_payments_subscriptions
-- ----------------------------
DROP TABLE IF EXISTS `freeform_payments_subscriptions`;
CREATE TABLE `freeform_payments_subscriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `integrationId` int NOT NULL,
  `submissionId` int NOT NULL,
  `planId` int NOT NULL,
  `resourceId` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `interval` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `intervalCount` smallint DEFAULT NULL,
  `last4` smallint DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `metadata` mediumtext COLLATE utf8mb3_unicode_ci,
  `errorCode` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `errorMessage` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `freeform_payments_subscriptions_integrationId_resourceId_unq_idx` (`integrationId`,`resourceId`),
  KEY `freeform_payments_subscriptions_submissionId_fk` (`submissionId`),
  KEY `freeform_payments_subscriptions_planId_fk` (`planId`),
  CONSTRAINT `freeform_payments_subscriptions_integrationId_fk` FOREIGN KEY (`integrationId`) REFERENCES `freeform_integrations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `freeform_payments_subscriptions_planId_fk` FOREIGN KEY (`planId`) REFERENCES `freeform_payments_subscription_plans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `freeform_payments_subscriptions_submissionId_fk` FOREIGN KEY (`submissionId`) REFERENCES `freeform_submissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_pdf_templates
-- ----------------------------
DROP TABLE IF EXISTS `freeform_pdf_templates`;
CREATE TABLE `freeform_pdf_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb3_unicode_ci,
  `fileName` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb3_unicode_ci NOT NULL,
  `sortOrder` int NOT NULL DEFAULT '0',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_rules
-- ----------------------------
DROP TABLE IF EXISTS `freeform_rules`;
CREATE TABLE `freeform_rules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `combinator` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_rules_buttons
-- ----------------------------
DROP TABLE IF EXISTS `freeform_rules_buttons`;
CREATE TABLE `freeform_rules_buttons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pageId` int NOT NULL,
  `button` varchar(10) COLLATE utf8mb3_unicode_ci NOT NULL,
  `display` varchar(10) COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_xupjiebeavahnimjthxvqienjerafcjglxdf` (`pageId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_rules_conditions
-- ----------------------------
DROP TABLE IF EXISTS `freeform_rules_conditions`;
CREATE TABLE `freeform_rules_conditions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ruleId` int NOT NULL,
  `fieldId` int NOT NULL,
  `operator` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_tekphvliunoyvidhxcoshujaohkpvkkguasq` (`ruleId`),
  KEY `fk_zinplqvssohsnlwgpxkjqdgghkbunozhctgd` (`fieldId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_rules_fields
-- ----------------------------
DROP TABLE IF EXISTS `freeform_rules_fields`;
CREATE TABLE `freeform_rules_fields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fieldId` int NOT NULL,
  `display` varchar(10) COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_rkmidgpguxagltdpczqcmbxuhicpkvndeifd` (`fieldId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_rules_integrations
-- ----------------------------
DROP TABLE IF EXISTS `freeform_rules_integrations`;
CREATE TABLE `freeform_rules_integrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `integrationId` int NOT NULL,
  `push` tinyint(1) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_integrationId` (`integrationId`),
  CONSTRAINT `fk_integrationId` FOREIGN KEY (`integrationId`) REFERENCES `freeform_forms_integrations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_integrations_ruleId` FOREIGN KEY (`id`) REFERENCES `freeform_rules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_rules_notifications
-- ----------------------------
DROP TABLE IF EXISTS `freeform_rules_notifications`;
CREATE TABLE `freeform_rules_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `notificationId` int NOT NULL,
  `send` tinyint(1) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_hlozgizghteiszxwdnkliduwajnulvsuwaza` (`notificationId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_rules_pages
-- ----------------------------
DROP TABLE IF EXISTS `freeform_rules_pages`;
CREATE TABLE `freeform_rules_pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pageId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_djleljaepcjcyuxayjwrluojizkpbnjqaehh` (`pageId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_rules_submit_form
-- ----------------------------
DROP TABLE IF EXISTS `freeform_rules_submit_form`;
CREATE TABLE `freeform_rules_submit_form` (
  `id` int NOT NULL AUTO_INCREMENT,
  `formId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_unevpgofvvwxdknxdcamyefiuzwottuwxiob` (`formId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_saved_forms
-- ----------------------------
DROP TABLE IF EXISTS `freeform_saved_forms`;
CREATE TABLE `freeform_saved_forms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sessionId` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `formId` int NOT NULL,
  `token` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `payload` mediumtext COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `freeform_saved_forms_token_idx` (`token`),
  KEY `freeform_saved_forms_dateCreated_idx` (`dateCreated`),
  KEY `freeform_saved_forms_sessionId_idx` (`sessionId`),
  KEY `freeform_saved_forms_formId_fk` (`formId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_session_context
-- ----------------------------
DROP TABLE IF EXISTS `freeform_session_context`;
CREATE TABLE `freeform_session_context` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contextKey` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `sessionId` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `formId` int NOT NULL,
  `propertyBag` longtext COLLATE utf8mb3_unicode_ci,
  `attributeBag` longtext COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `freeform_session_context_contextKey_formId_idx` (`contextKey`,`formId`),
  KEY `freeform_session_context_sessionId_idx` (`sessionId`),
  KEY `freeform_session_context_formId_fk` (`formId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_spam_reason
-- ----------------------------
DROP TABLE IF EXISTS `freeform_spam_reason`;
CREATE TABLE `freeform_spam_reason` (
  `id` int NOT NULL AUTO_INCREMENT,
  `submissionId` int NOT NULL,
  `reasonType` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `reasonMessage` text COLLATE utf8mb3_unicode_ci,
  `reasonValue` longtext COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `freeform_spam_reason_submissionId_reasonType_idx` (`submissionId`,`reasonType`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_statuses
-- ----------------------------
DROP TABLE IF EXISTS `freeform_statuses`;
CREATE TABLE `freeform_statuses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `color` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sortOrder` int DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `handle` (`handle`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_submission_notes
-- ----------------------------
DROP TABLE IF EXISTS `freeform_submission_notes`;
CREATE TABLE `freeform_submission_notes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `submissionId` int NOT NULL,
  `note` text COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `freeform_submission_notes_submissionId_fk` (`submissionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_submissions
-- ----------------------------
DROP TABLE IF EXISTS `freeform_submissions`;
CREATE TABLE `freeform_submissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `incrementalId` int NOT NULL,
  `userId` int DEFAULT NULL,
  `statusId` int DEFAULT NULL,
  `formId` int NOT NULL,
  `token` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `ip` varchar(46) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `isSpam` tinyint(1) DEFAULT '0',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  `isHidden` tinyint(1) NOT NULL DEFAULT '0',
  `requestId` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `freeform_submissions_incrementalId_unq_idx` (`incrementalId`),
  UNIQUE KEY `freeform_submissions_token_unq_idx` (`token`),
  KEY `freeform_submissions_userId_fk` (`userId`),
  KEY `freeform_submissions_formId_fk` (`formId`),
  KEY `freeform_submissions_statusId_fk` (`statusId`)
) ENGINE=InnoDB AUTO_INCREMENT=27712 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_submissions_contact_form_1
-- ----------------------------
DROP TABLE IF EXISTS `freeform_submissions_contact_form_1`;
CREATE TABLE `freeform_submissions_contact_form_1` (
  `id` int NOT NULL,
  `t_form_first_name_2` text COLLATE utf8mb3_unicode_ci,
  `t_form_last_name_3` text COLLATE utf8mb3_unicode_ci,
  `t_form_email_4` text COLLATE utf8mb3_unicode_ci,
  `t_form_phone_5` text COLLATE utf8mb3_unicode_ci,
  `t_form_subject_6` text COLLATE utf8mb3_unicode_ci,
  `t_form_message_7` text COLLATE utf8mb3_unicode_ci,
  `countries_1` text COLLATE utf8mb3_unicode_ci,
  `t_form_communications_8` text COLLATE utf8mb3_unicode_ci,
  `region_11` text COLLATE utf8mb3_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_submissions_tracking_parameters
-- ----------------------------
DROP TABLE IF EXISTS `freeform_submissions_tracking_parameters`;
CREATE TABLE `freeform_submissions_tracking_parameters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `submissionId` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `submissionId_name` (`submissionId`,`name`),
  CONSTRAINT `fk-freeform_submissions_tracking_parameters-submissionId` FOREIGN KEY (`submissionId`) REFERENCES `freeform_submissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_survey_preferences
-- ----------------------------
DROP TABLE IF EXISTS `freeform_survey_preferences`;
CREATE TABLE `freeform_survey_preferences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `fieldId` int NOT NULL,
  `chartType` varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_nwqflthewbgxvjbzbcpujzxnlnhxlqfztfiz` (`userId`),
  KEY `fk_nhdjnsvnecmsqsqcyzxicfghbxkycnuvzbnw` (`fieldId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_unfinalized_files
-- ----------------------------
DROP TABLE IF EXISTS `freeform_unfinalized_files`;
CREATE TABLE `freeform_unfinalized_files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assetId` int NOT NULL,
  `fieldHandle` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `formToken` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `freeform_unfinalized_files_fieldHandle_formToken_idx` (`fieldHandle`,`formToken`),
  KEY `fk_crbuyeashbtujsdnmsmbhfirdpuexwvmkzmc` (`assetId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_webhooks
-- ----------------------------
DROP TABLE IF EXISTS `freeform_webhooks`;
CREATE TABLE `freeform_webhooks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `webhook` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `settings` text COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `freeform_webhooks_type_idx` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for freeform_webhooks_form_relations
-- ----------------------------
DROP TABLE IF EXISTS `freeform_webhooks_form_relations`;
CREATE TABLE `freeform_webhooks_form_relations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `webhookId` int NOT NULL,
  `formId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `freeform_webhooks_form_relations_webhookId_idx` (`webhookId`),
  KEY `freeform_webhooks_form_relations_formId_idx` (`formId`),
  CONSTRAINT `freeform_webhooks_form_relations_formId_fk` FOREIGN KEY (`formId`) REFERENCES `freeform_forms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `freeform_webhooks_form_relations_webhookId_fk` FOREIGN KEY (`webhookId`) REFERENCES `freeform_webhooks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for globalsets
-- ----------------------------
DROP TABLE IF EXISTS `globalsets`;
CREATE TABLE `globalsets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `fieldLayoutId` int DEFAULT NULL,
  `sortOrder` smallint unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_akfiydowmukuokmkozzqjognupvwqykknajg` (`name`),
  KEY `idx_yveguopoqskqgfkoxgltizfqlnbihbagvrbu` (`handle`),
  KEY `idx_obkhhosvxawwztxxiubkglxwjvyjxlqglhvt` (`fieldLayoutId`),
  KEY `idx_hsmjwrkuqccriwucflwtmjidyfzactbsozez` (`sortOrder`),
  CONSTRAINT `fk_jrrbywfwqmkcgfwuowvbbazjqwsgnktjwtqq` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_uicnrfqqansmplmhjemjhimlhbsixcoqzrzw` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=775 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for gqlschemas
-- ----------------------------
DROP TABLE IF EXISTS `gqlschemas`;
CREATE TABLE `gqlschemas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `scope` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `isPublic` tinyint(1) NOT NULL DEFAULT '0',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  CONSTRAINT `gqlschemas_chk_1` CHECK (json_valid(`scope`))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for gqltokens
-- ----------------------------
DROP TABLE IF EXISTS `gqltokens`;
CREATE TABLE `gqltokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `accessToken` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `expiryDate` datetime DEFAULT NULL,
  `lastUsed` datetime DEFAULT NULL,
  `schemaId` int DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_gbvovxwlzbamgfqfygmpndbhrsspahnzlqrc` (`accessToken`),
  UNIQUE KEY `idx_fsxcqxmbragpaqlvadkaomuoyijdasqbhzmp` (`name`),
  KEY `fk_uopvbruwiooewwawancxpegesfcqficqugvq` (`schemaId`),
  CONSTRAINT `fk_uopvbruwiooewwawancxpegesfcqficqugvq` FOREIGN KEY (`schemaId`) REFERENCES `gqlschemas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for imagetransformindex
-- ----------------------------
DROP TABLE IF EXISTS `imagetransformindex`;
CREATE TABLE `imagetransformindex` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assetId` int NOT NULL,
  `transformer` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `format` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `transformString` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `fileExists` tinyint(1) NOT NULL DEFAULT '0',
  `inProgress` tinyint(1) NOT NULL DEFAULT '0',
  `error` tinyint(1) NOT NULL DEFAULT '0',
  `dateIndexed` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_ttpdrzfsrswzmbnscbrplllqqcnbfawnwbnu` (`assetId`,`format`,`transformString`),
  KEY `idx_hxaiozqzpvfkwambwuacrkkegpxzrrykxvvq` (`assetId`,`transformString`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for imagetransforms
-- ----------------------------
DROP TABLE IF EXISTS `imagetransforms`;
CREATE TABLE `imagetransforms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `mode` enum('stretch','fit','crop','letterbox') COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'crop',
  `position` enum('top-left','top-center','top-right','center-left','center-center','center-right','bottom-left','bottom-center','bottom-right') COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'center-center',
  `width` int unsigned DEFAULT NULL,
  `height` int unsigned DEFAULT NULL,
  `format` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `quality` int DEFAULT NULL,
  `interlace` enum('none','line','plane','partition') COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'none',
  `fill` varchar(11) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `upscale` tinyint(1) NOT NULL DEFAULT '1',
  `parameterChangeTime` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_lwphkpzyrpqycyypohuvvexlznrbropokdfe` (`name`),
  KEY `idx_jnqbrifzvmepkcaokfogimvunwszlhqomphb` (`handle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for info
-- ----------------------------
DROP TABLE IF EXISTS `info`;
CREATE TABLE `info` (
  `id` int NOT NULL AUTO_INCREMENT,
  `version` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL,
  `schemaVersion` varchar(15) COLLATE utf8mb3_unicode_ci NOT NULL,
  `maintenance` tinyint(1) NOT NULL DEFAULT '0',
  `configVersion` char(12) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '000000000000',
  `fieldVersion` char(12) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '000000000000',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for lenz_linkfield
-- ----------------------------
DROP TABLE IF EXISTS `lenz_linkfield`;
CREATE TABLE `lenz_linkfield` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elementId` int NOT NULL,
  `fieldId` int NOT NULL,
  `siteId` int NOT NULL,
  `type` varchar(63) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `linkedUrl` text COLLATE utf8mb3_unicode_ci,
  `linkedId` int DEFAULT NULL,
  `linkedSiteId` int DEFAULT NULL,
  `linkedTitle` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `payload` text COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_cftgeubdmsnkmuvqnyzbjgstadlptimibnnk` (`elementId`,`siteId`,`fieldId`),
  KEY `idx_zdnioddhuqfuegzpsbszgxankvfwxbrmnexd` (`fieldId`),
  KEY `idx_lgxwfbvlovvhwzuooffqlsbsuxkymoxhmdlm` (`siteId`),
  KEY `fk_kdccwyzekepwgwtuvwonpfxifgyqsmwlvria` (`linkedId`),
  KEY `fk_hjaopdpifjhfzhnfdxmvdkimeocduvgjqqro` (`linkedSiteId`),
  CONSTRAINT `fk_cubcrmrrenvfcdydakwvcqihtfjszcoqzbza` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hjaopdpifjhfzhnfdxmvdkimeocduvgjqqro` FOREIGN KEY (`linkedSiteId`) REFERENCES `sites` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `fk_kdccwyzekepwgwtuvwonpfxifgyqsmwlvria` FOREIGN KEY (`linkedId`) REFERENCES `elements` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `fk_pjjlrqcyobjukmuctlfvoddctoinpeqgrjhs` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24722 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for matrixblocks
-- ----------------------------
DROP TABLE IF EXISTS `matrixblocks`;
CREATE TABLE `matrixblocks` (
  `id` int NOT NULL,
  `ownerId` int NOT NULL,
  `fieldId` int NOT NULL,
  `typeId` int NOT NULL,
  `sortOrder` smallint unsigned DEFAULT NULL,
  `deletedWithOwner` tinyint(1) DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_snbxgfngutytrjxmwtwntlvpkjpchsijbuac` (`ownerId`),
  KEY `idx_npffkiinismjqbjnxadtqnttoocsgutgjslk` (`fieldId`),
  KEY `idx_ibxaexpxsxkgdtkmccnyosmgkbbcpahwttjd` (`typeId`),
  KEY `idx_uhdkwkapriqfnvzmfduhssyvruhiztfadlam` (`sortOrder`),
  CONSTRAINT `fk_aucuvyuvyzgahanlzfwvnmcofphjubiwpeoc` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_gjybhqrkjmwzoqsuymqpiypoiwiqersvuoyc` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jdvqboihjhufdydpnxubcmntosvwsfdxinug` FOREIGN KEY (`typeId`) REFERENCES `matrixblocktypes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_nfumhrjhsvcfyvhkvbkrjizwyakkqitzspzz` FOREIGN KEY (`ownerId`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for matrixblocktypes
-- ----------------------------
DROP TABLE IF EXISTS `matrixblocktypes`;
CREATE TABLE `matrixblocktypes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fieldId` int NOT NULL,
  `fieldLayoutId` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `sortOrder` smallint unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_txsyjkdahftsrzszcrfzbdihmnvrwvbcvasj` (`name`,`fieldId`),
  KEY `idx_wbwjmupqgwefovoeojqvfwkzulshqkvkajyi` (`handle`,`fieldId`),
  KEY `idx_zzxvkbnpihfufqdjoyagawchbcyqtcmwyxvo` (`fieldId`),
  KEY `idx_ykiqmyhmyrzpqxqpwsyqbfnahpfgazjdouto` (`fieldLayoutId`),
  CONSTRAINT `fk_dwhskqkfzftbtuzzajjctqsbxvwqnsiylbct` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_zfbbzwaitubfulfvrwyueqdfzchzgploxqbl` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for matrixcontent_contactinfo
-- ----------------------------
DROP TABLE IF EXISTS `matrixcontent_contactinfo`;
CREATE TABLE `matrixcontent_contactinfo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elementId` int NOT NULL,
  `siteId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `field_infoLine_contactInfoLine_ldswqinp` text COLLATE utf8mb4_general_ci,
  `field_infoLine_marginBottom_grfwusjn` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_gfotfgvpzgxmkslaxtwfymibfcqvtodqnnpu` (`elementId`,`siteId`),
  KEY `fk_mtnkvlwydtukvitdcfafqvtordeuyuqnxmro` (`siteId`),
  CONSTRAINT `fk_mtnkvlwydtukvitdcfafqvtordeuyuqnxmro` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_nzxbkzecphrmmoxjtptopequhihsitbvmtvy` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1336 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for matrixcontent_countries
-- ----------------------------
DROP TABLE IF EXISTS `matrixcontent_countries`;
CREATE TABLE `matrixcontent_countries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elementId` int NOT NULL,
  `siteId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  `field_region_regionName_nqvsqrue` text,
  `field_region_emailAddress_iuzvhgvz` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_eqxfglsrsnxkgrlyypwybkzoctktfdzaomjq` (`elementId`,`siteId`),
  KEY `fk_ccvftzpphvqysaykjtxicqrkonmpohylsysr` (`siteId`),
  CONSTRAINT `fk_ccvftzpphvqysaykjtxicqrkonmpohylsysr` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rgkwgcoeksvhuulvsuuhwxcwutojjqlzdhzb` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Table structure for matrixcontent_links
-- ----------------------------
DROP TABLE IF EXISTS `matrixcontent_links`;
CREATE TABLE `matrixcontent_links` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elementId` int NOT NULL,
  `siteId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `field_linksBlock_linkField_kbfciljq` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_gazvybnikghrpmxcusgjptjqyavivpxltywc` (`elementId`,`siteId`),
  KEY `fk_kdgjsjhvjjzphaaetxtgzzjykyousoqzbdif` (`siteId`),
  CONSTRAINT `fk_gmxrtdpsawadlgbjxquatrbcdsujyqlmjtka` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kdgjsjhvjjzphaaetxtgzzjykyousoqzbdif` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for matrixcontent_resources
-- ----------------------------
DROP TABLE IF EXISTS `matrixcontent_resources`;
CREATE TABLE `matrixcontent_resources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elementId` int NOT NULL,
  `siteId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `field_resourceBlock_label_vxtwoqqj` text COLLATE utf8mb4_general_ci,
  `field_resourceBlock_shortDescription_rdwpymiu` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_kwxvxktscplghzugaqigrxcllsrtvlfrzjqr` (`elementId`,`siteId`),
  KEY `fk_lygslzccmbfcwqhyewhqlunisqrntmlcvquw` (`siteId`),
  CONSTRAINT `fk_iwsovcqgetugdzaankdooarxuucfxtyrssbx` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lygslzccmbfcwqhyewhqlunisqrntmlcvquw` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for matrixcontent_sitelogo
-- ----------------------------
DROP TABLE IF EXISTS `matrixcontent_sitelogo`;
CREATE TABLE `matrixcontent_sitelogo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elementId` int NOT NULL,
  `siteId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  `field_languageLogo_languageCode_bderrdno` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_wxckqucbcwhlrskpddqjupymalsidzwoompp` (`elementId`,`siteId`),
  KEY `fk_qtzomrtvtfwbrwcgwtqasbosojpfpbdsyxgd` (`siteId`),
  CONSTRAINT `fk_qtzomrtvtfwbrwcgwtqasbosojpfpbdsyxgd` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ynpoalpaxbitfvzvdgkmqcrqzirhdbkdecsh` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `track` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `applyTime` datetime NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_nxppzkphfunqhhkyhftcbxlbcndyeyivsyka` (`track`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=562 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for navigation_navs
-- ----------------------------
DROP TABLE IF EXISTS `navigation_navs`;
CREATE TABLE `navigation_navs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `structureId` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `instructions` text COLLATE utf8mb3_unicode_ci,
  `sortOrder` smallint unsigned DEFAULT NULL,
  `propagationMethod` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'all',
  `maxNodes` int DEFAULT NULL,
  `maxNodesSettings` text COLLATE utf8mb3_unicode_ci,
  `permissions` text COLLATE utf8mb3_unicode_ci,
  `fieldLayoutId` int DEFAULT NULL,
  `defaultPlacement` enum('beginning','end') COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'end',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `dateDeleted` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_ivbercflvmzufxljqlxmjjgfpksnhbbxtyty` (`handle`),
  KEY `idx_xlkdlwaazovcsahpqptgpqhkfdkbvfappgtx` (`structureId`),
  KEY `idx_ivxkeydpgkcsbybhjcpikeirhxyrghjswqfn` (`fieldLayoutId`),
  KEY `idx_epwkdfnmdtviwhhqsqyywiklfvskxvuuzjeg` (`dateDeleted`),
  CONSTRAINT `fk_ciuobuelwzcgbknwadccuomuufjqzwcglnzr` FOREIGN KEY (`structureId`) REFERENCES `structures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_deegpiotlcynskunucsftfvargahdpocyiwp` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for navigation_navs_sites
-- ----------------------------
DROP TABLE IF EXISTS `navigation_navs_sites`;
CREATE TABLE `navigation_navs_sites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `navId` int NOT NULL,
  `siteId` int NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_ggpmeibsqmioohafnralncgenhkwjikoqeax` (`navId`,`siteId`),
  KEY `idx_snmxtjuvrctmnyamafzbcdupxkphisyasvfu` (`siteId`),
  CONSTRAINT `fk_vwswvrudikphuopsyokpsmdlmetbcslmlfcx` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_whmdxmgdljftxleyxitoauwvxgronglhhigz` FOREIGN KEY (`navId`) REFERENCES `navigation_navs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for navigation_nodes
-- ----------------------------
DROP TABLE IF EXISTS `navigation_nodes`;
CREATE TABLE `navigation_nodes` (
  `id` int NOT NULL,
  `elementId` int DEFAULT NULL,
  `navId` int NOT NULL,
  `parentId` int DEFAULT NULL,
  `url` text COLLATE utf8mb3_unicode_ci,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `classes` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `urlSuffix` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `customAttributes` text COLLATE utf8mb3_unicode_ci,
  `data` text COLLATE utf8mb3_unicode_ci,
  `newWindow` tinyint(1) DEFAULT '0',
  `deletedWithNav` tinyint(1) DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_vohiwlibcjhhnsbknccxtbmyfkkfzzgfjjtv` (`navId`),
  KEY `fk_ejejalflzqwtxfgyxjuyzejhmrgxmommucwm` (`elementId`),
  CONSTRAINT `fk_cmpupkravmwtskkozaxebgebxztnycadjweg` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ejejalflzqwtxfgyxjuyzejhmrgxmommucwm` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fbatddagrwkfsebnfwwlzgfzbrnudvbacvih` FOREIGN KEY (`navId`) REFERENCES `navigation_navs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for neoblocks
-- ----------------------------
DROP TABLE IF EXISTS `neoblocks`;
CREATE TABLE `neoblocks` (
  `id` int NOT NULL,
  `primaryOwnerId` int NOT NULL,
  `fieldId` int NOT NULL,
  `typeId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ekbsshuncqocqtgahcllmihunogejyjbyztw` (`primaryOwnerId`),
  KEY `idx_ujubkamgqgtsbyijghijpysytkltqzkonjlj` (`fieldId`),
  KEY `idx_lbxblfkibeqotgczqyttaeqfmrocbkmzgvtr` (`typeId`),
  CONSTRAINT `fk_gbpineygditikgjpvlowectnjlloruslgoxl` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_uxayomvfrozulhwfjlljnodjijvptdclzfpj` FOREIGN KEY (`typeId`) REFERENCES `neoblocktypes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_yvawuxzlqblqndindpsrfltskbhcaqqcmnel` FOREIGN KEY (`primaryOwnerId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_zrriztmtgookvwurjrffvwykokyghoepebxd` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for neoblockstructures
-- ----------------------------
DROP TABLE IF EXISTS `neoblockstructures`;
CREATE TABLE `neoblockstructures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `structureId` int NOT NULL,
  `ownerId` int NOT NULL,
  `siteId` int DEFAULT NULL,
  `fieldId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_ejpjfvcqmgvcusacyqgcrkvohrsvjbwwjnon` (`structureId`),
  KEY `idx_xsxaakvvanwoiveynfbznounqomkewenrrxo` (`ownerId`),
  KEY `idx_tsnxalkzjahqsfcykewhtiruayszjeegfbsd` (`siteId`),
  KEY `idx_hwkscvawobjehclgorpcqmnuuktyfgpvdkuq` (`fieldId`),
  CONSTRAINT `fk_anlyjzsdhrxnqkbesfxpgqxwmstxrfmogkxt` FOREIGN KEY (`ownerId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_khofomclvoygweewpktujzxrbczpixdmjeru` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_nhrkguigynzjequzsbstxuxpudfncqirontu` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ozbkomuvrvacouzitbpeziwhdqjetcprpszv` FOREIGN KEY (`structureId`) REFERENCES `structures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21299 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for neoblocktypegroups
-- ----------------------------
DROP TABLE IF EXISTS `neoblocktypegroups`;
CREATE TABLE `neoblocktypegroups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fieldId` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `sortOrder` smallint unsigned DEFAULT NULL,
  `alwaysShowDropdown` tinyint(1) DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_ptfnysvxtxabgkmnisyivffyqgfwnewjptfe` (`name`,`fieldId`),
  KEY `idx_zqhbuepwwlrfftrxvoeczcppghghefazlrlw` (`fieldId`),
  CONSTRAINT `fk_tzkzzfyvsbfoomjwdxpnjcybkqvspjopwngk` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for neoblocktypes
-- ----------------------------
DROP TABLE IF EXISTS `neoblocktypes`;
CREATE TABLE `neoblocktypes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fieldId` int NOT NULL,
  `fieldLayoutId` int DEFAULT NULL,
  `groupId` int DEFAULT NULL,
  `entryTypeId` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `iconFilename` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `iconId` int DEFAULT NULL,
  `color` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `minBlocks` smallint unsigned DEFAULT '0',
  `maxBlocks` smallint unsigned DEFAULT NULL,
  `minSiblingBlocks` smallint unsigned DEFAULT '0',
  `maxSiblingBlocks` smallint unsigned DEFAULT '0',
  `minChildBlocks` smallint unsigned DEFAULT '0',
  `maxChildBlocks` smallint unsigned DEFAULT NULL,
  `groupChildBlockTypes` tinyint(1) NOT NULL DEFAULT '1',
  `childBlocks` text COLLATE utf8mb3_unicode_ci,
  `topLevel` tinyint(1) NOT NULL DEFAULT '1',
  `ignorePermissions` tinyint(1) NOT NULL DEFAULT '1',
  `sortOrder` smallint unsigned DEFAULT NULL,
  `conditions` text COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_fcpkzsmhwnlfhitqmmmbzhcussutwpnqbyhp` (`handle`,`fieldId`),
  KEY `idx_hirdjqfcgljsqkspuznkkysmlsvcfdygiwtj` (`name`,`fieldId`),
  KEY `idx_favchwagjvgiacglaivfinxowbgiourwfjth` (`fieldId`),
  KEY `idx_acslydwhpwyrosmflrascnghmwvxflvrwjgx` (`fieldLayoutId`),
  KEY `idx_gtdaijpwazglbxifrwvgroybremwghcmlmyq` (`groupId`),
  KEY `fk_jppjmiqlkwyfhlicrookzypauakzbttvecvf` (`iconId`),
  KEY `fk_fvcefqtiecaikizqqdorltjvssxbxllpowbe` (`entryTypeId`),
  CONSTRAINT `fk_fvcefqtiecaikizqqdorltjvssxbxllpowbe` FOREIGN KEY (`entryTypeId`) REFERENCES `entrytypes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_jppjmiqlkwyfhlicrookzypauakzbttvecvf` FOREIGN KEY (`iconId`) REFERENCES `assets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_jzzwmycfijekvbuzgvfvfuxcwagvjnbrhhbc` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lpwsmvscabzjafubpwpoazkgorvdigavshgv` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_zknjbzhzksozcheuxgelqmawfrmzgebsqcxg` FOREIGN KEY (`groupId`) REFERENCES `neoblocktypegroups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for plugins
-- ----------------------------
DROP TABLE IF EXISTS `plugins`;
CREATE TABLE `plugins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `version` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `schemaVersion` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `installDate` datetime NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_tmdeanavgerfekqpinurmwijlwrawyjfgtep` (`handle`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for projectconfig
-- ----------------------------
DROP TABLE IF EXISTS `projectconfig`;
CREATE TABLE `projectconfig` (
  `path` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for queue
-- ----------------------------
DROP TABLE IF EXISTS `queue`;
CREATE TABLE `queue` (
  `id` int NOT NULL AUTO_INCREMENT,
  `channel` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'queue',
  `job` longblob NOT NULL,
  `description` text COLLATE utf8mb3_unicode_ci,
  `timePushed` int NOT NULL,
  `ttr` int NOT NULL,
  `delay` int NOT NULL DEFAULT '0',
  `priority` int unsigned NOT NULL DEFAULT '1024',
  `dateReserved` datetime DEFAULT NULL,
  `timeUpdated` int DEFAULT NULL,
  `progress` smallint NOT NULL DEFAULT '0',
  `progressLabel` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `attempt` int DEFAULT NULL,
  `fail` tinyint(1) DEFAULT '0',
  `dateFailed` datetime DEFAULT NULL,
  `error` text COLLATE utf8mb3_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_cpcceeklbcdeuujefmguiaqirpdmlydbyegk` (`channel`,`fail`,`timeUpdated`,`timePushed`),
  KEY `idx_bqfyfbtinzjyxhwmdbmfjfdyxwmssfppsgzf` (`channel`,`fail`,`timeUpdated`,`delay`)
) ENGINE=InnoDB AUTO_INCREMENT=542225 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for recoverycodes
-- ----------------------------
DROP TABLE IF EXISTS `recoverycodes`;
CREATE TABLE `recoverycodes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `recoveryCodes` text COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_fhonuotojrkcitiyqtvxjtsfpzznoabixluh` (`userId`),
  CONSTRAINT `fk_fhonuotojrkcitiyqtvxjtsfpzznoabixluh` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for relations
-- ----------------------------
DROP TABLE IF EXISTS `relations`;
CREATE TABLE `relations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fieldId` int NOT NULL,
  `sourceId` int NOT NULL,
  `sourceSiteId` int DEFAULT NULL,
  `targetId` int NOT NULL,
  `sortOrder` smallint unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_meutgxshqkhjtqernxnsskyoaisqryujorgd` (`fieldId`,`sourceId`,`sourceSiteId`,`targetId`),
  KEY `idx_rfogsfxwuyytehckhvecypmgvwduqsjhielx` (`sourceId`),
  KEY `idx_lvdoyfpygxlgycoazbcgrxrqbdiwtfzdisnn` (`targetId`),
  KEY `idx_eoqxxfemidxnknxkobiylivucteelbgrgycy` (`sourceSiteId`),
  CONSTRAINT `fk_dolnqqxvtrymmxevzpylpdutrzvbalhgjgdo` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dxsqdcrzuxqfywznqncleajxypnooaitjnwd` FOREIGN KEY (`sourceId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mpuzcgwwghpfgubuzfmcouwomsyhfftqjycq` FOREIGN KEY (`sourceSiteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8589 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for resourcepaths
-- ----------------------------
DROP TABLE IF EXISTS `resourcepaths`;
CREATE TABLE `resourcepaths` (
  `hash` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for retour_redirects
-- ----------------------------
DROP TABLE IF EXISTS `retour_redirects`;
CREATE TABLE `retour_redirects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  `siteId` int DEFAULT NULL,
  `associatedElementId` int NOT NULL,
  `enabled` tinyint(1) DEFAULT '1',
  `redirectSrcUrl` varchar(255) DEFAULT '',
  `redirectSrcUrlParsed` varchar(255) DEFAULT '',
  `redirectSrcMatch` varchar(32) DEFAULT 'pathonly',
  `redirectMatchType` varchar(32) DEFAULT 'exactmatch',
  `redirectDestUrl` varchar(255) DEFAULT '',
  `redirectHttpCode` int DEFAULT '301',
  `hitCount` int DEFAULT '1',
  `hitLastTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vvvtubnzqwltgeeeqvdqsfaiyewdmvmqovvi` (`redirectSrcUrlParsed`),
  KEY `idx_qarotpgmovhtlgobhvjwrvecbbjnkqcwxley` (`redirectSrcUrl`),
  KEY `idx_opscpcsssmgbnmdeuibprdczablrppwniute` (`siteId`),
  KEY `fk_fwoovbhajrfbarvtmqcwqnbztmjxtitvpmgz` (`associatedElementId`),
  KEY `idx_qmszrmneiuihsnejkomdobhhnbprstaiiqfh` (`redirectMatchType`),
  KEY `idx_djiakmjrjlrybolecoonoscvnxybfekwuudw` (`redirectMatchType`),
  CONSTRAINT `fk_fwoovbhajrfbarvtmqcwqnbztmjxtitvpmgz` FOREIGN KEY (`associatedElementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Table structure for retour_static_redirects
-- ----------------------------
DROP TABLE IF EXISTS `retour_static_redirects`;
CREATE TABLE `retour_static_redirects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  `siteId` int DEFAULT NULL,
  `associatedElementId` int NOT NULL,
  `enabled` tinyint(1) DEFAULT '1',
  `redirectSrcUrl` varchar(255) DEFAULT '',
  `redirectSrcUrlParsed` varchar(255) DEFAULT '',
  `redirectSrcMatch` varchar(32) DEFAULT 'pathonly',
  `redirectMatchType` varchar(32) DEFAULT 'exactmatch',
  `redirectDestUrl` varchar(255) DEFAULT '',
  `redirectHttpCode` int DEFAULT '301',
  `priority` int DEFAULT '5',
  `hitCount` int DEFAULT '1',
  `hitLastTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_wxyudpdarnmzjikudxjpayvnkywizgiodhrk` (`redirectSrcUrlParsed`),
  KEY `idx_jlsvriwrbyvzcppjuujphzfqdtwsuzbqhrke` (`redirectSrcUrl`),
  KEY `idx_qbspwakrqetecijylusicyijlzlnxwvqoqaq` (`siteId`),
  KEY `idx_sjlqcapugqmhssfjdowqaczrabcznlsdvfdl` (`redirectMatchType`),
  KEY `idx_lslslvpnvzwstgpbjhqxkfukviatovdzywdv` (`redirectMatchType`),
  CONSTRAINT `fk_hgdspniuxqvpfbfbnvewffbyjqjnmtqxoaoe` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Table structure for retour_stats
-- ----------------------------
DROP TABLE IF EXISTS `retour_stats`;
CREATE TABLE `retour_stats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  `siteId` int DEFAULT NULL,
  `redirectSrcUrl` varchar(255) DEFAULT '',
  `referrerUrl` varchar(2000) DEFAULT '',
  `remoteIp` varchar(45) DEFAULT '',
  `userAgent` varchar(255) DEFAULT '',
  `exceptionMessage` varchar(255) DEFAULT '',
  `exceptionFilePath` varchar(255) DEFAULT '',
  `exceptionFileLine` int DEFAULT '0',
  `hitCount` int DEFAULT '1',
  `hitLastTime` datetime DEFAULT NULL,
  `handledByRetour` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_xydvldowbpqwsknrhffwkwtuunovkbnnbprf` (`redirectSrcUrl`),
  KEY `idx_qrujxtbqhwvtxchkrbtvuzvrazkewjzxfqxr` (`siteId`),
  CONSTRAINT `fk_vlfqenooarkrivqyqanedrhksivsxztbwugm` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=89397 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Table structure for revisions
-- ----------------------------
DROP TABLE IF EXISTS `revisions`;
CREATE TABLE `revisions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `canonicalId` int NOT NULL,
  `creatorId` int DEFAULT NULL,
  `num` int NOT NULL,
  `notes` text COLLATE utf8mb3_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_rmwufvdgixoielxvaingzbxozqchmpbqefqb` (`canonicalId`,`num`),
  KEY `fk_remxphqhmxlioakpunmxambwnwxatpunqfgy` (`creatorId`),
  CONSTRAINT `fk_remxphqhmxlioakpunmxambwnwxatpunqfgy` FOREIGN KEY (`creatorId`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_vhvmtpzhyhhrgyygyoyhgugvppqqflphssco` FOREIGN KEY (`canonicalId`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1146 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for searchindex
-- ----------------------------
DROP TABLE IF EXISTS `searchindex`;
CREATE TABLE `searchindex` (
  `elementId` int NOT NULL,
  `attribute` varchar(25) COLLATE utf8mb3_unicode_ci NOT NULL,
  `fieldId` int NOT NULL,
  `siteId` int NOT NULL,
  `keywords` text COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`elementId`,`attribute`,`fieldId`,`siteId`),
  FULLTEXT KEY `idx_hmmaapirynyifqogldxlbfekyjmgxzvmkeos` (`keywords`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for searchindexqueue
-- ----------------------------
DROP TABLE IF EXISTS `searchindexqueue`;
CREATE TABLE `searchindexqueue` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elementId` int NOT NULL,
  `siteId` int NOT NULL,
  `reserved` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_gjiednhjkyihtxmirgyaccpujgwjvvnlvrws` (`elementId`,`siteId`,`reserved`),
  CONSTRAINT `fk_ksibutaoiddyjezcviggqakwhcbhcboxrcli` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for searchindexqueue_fields
-- ----------------------------
DROP TABLE IF EXISTS `searchindexqueue_fields`;
CREATE TABLE `searchindexqueue_fields` (
  `jobId` int NOT NULL,
  `fieldHandle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`jobId`,`fieldHandle`),
  UNIQUE KEY `idx_kpryrhhghovgrrlatyxcppdnzfgkhuxpkmcu` (`jobId`,`fieldHandle`),
  CONSTRAINT `fk_tlzpvbvudnxsjxiamcncftjypglxaukmkfhh` FOREIGN KEY (`jobId`) REFERENCES `searchindexqueue` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for sections
-- ----------------------------
DROP TABLE IF EXISTS `sections`;
CREATE TABLE `sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `structureId` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` enum('single','channel','structure') COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'channel',
  `enableVersioning` tinyint(1) NOT NULL DEFAULT '0',
  `maxAuthors` smallint unsigned DEFAULT NULL,
  `propagationMethod` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'all',
  `defaultPlacement` enum('beginning','end') COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'end',
  `previewTargets` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `dateDeleted` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_jyrcvnlyshagynpjiosyswftzjixphxuzokq` (`handle`),
  KEY `idx_rseopgxngcfhkmgnalonwvilgiojruvynivc` (`name`),
  KEY `idx_qryxuzsgwwqctzpkbafbjpyqpvyolqthfppd` (`structureId`),
  KEY `idx_djbjfgsevzfpvnczpfvsvciufzjbfvxhyadd` (`dateDeleted`),
  CONSTRAINT `fk_gokoodjbguxrkfpbqevljvpxkcvetxefsdtr` FOREIGN KEY (`structureId`) REFERENCES `structures` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sections_chk_1` CHECK (json_valid(`previewTargets`))
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for sections_entrytypes
-- ----------------------------
DROP TABLE IF EXISTS `sections_entrytypes`;
CREATE TABLE `sections_entrytypes` (
  `sectionId` int NOT NULL,
  `typeId` int NOT NULL,
  `sortOrder` smallint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb3_unicode_ci,
  PRIMARY KEY (`sectionId`,`typeId`),
  KEY `fk_fhpiaghcydquhyiyjhkqmfuyknjrspxmqhuf` (`typeId`),
  CONSTRAINT `fk_fhpiaghcydquhyiyjhkqmfuyknjrspxmqhuf` FOREIGN KEY (`typeId`) REFERENCES `entrytypes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hytcexaaxenlxmioecrcbklpyakjggkuloyj` FOREIGN KEY (`sectionId`) REFERENCES `sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for sections_sites
-- ----------------------------
DROP TABLE IF EXISTS `sections_sites`;
CREATE TABLE `sections_sites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sectionId` int NOT NULL,
  `siteId` int NOT NULL,
  `hasUrls` tinyint(1) NOT NULL DEFAULT '1',
  `uriFormat` text COLLATE utf8mb3_unicode_ci,
  `template` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `enabledByDefault` tinyint(1) NOT NULL DEFAULT '1',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_emzpvhmiqgodtmaukwnlzexeqmxffhqjxdka` (`sectionId`,`siteId`),
  KEY `idx_xhytatyomigqmksdvaroaikymaagqouxrekm` (`siteId`),
  CONSTRAINT `fk_nuetpvvnzbzbigzsimrngirmyxwtrjhjzilk` FOREIGN KEY (`sectionId`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_uqgdbwqofhvlypritqsqskyoflryeiiufqvk` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for seomatic_metabundles
-- ----------------------------
DROP TABLE IF EXISTS `seomatic_metabundles`;
CREATE TABLE `seomatic_metabundles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  `bundleVersion` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `sourceBundleType` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `sourceId` int DEFAULT NULL,
  `sourceName` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `sourceHandle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `sourceType` varchar(64) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `typeId` int DEFAULT NULL,
  `sourceTemplate` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `sourceSiteId` int DEFAULT NULL,
  `sourceAltSiteSettings` text COLLATE utf8mb3_unicode_ci,
  `sourceDateUpdated` datetime NOT NULL,
  `metaGlobalVars` text COLLATE utf8mb3_unicode_ci,
  `metaSiteVars` text COLLATE utf8mb3_unicode_ci,
  `metaSitemapVars` text COLLATE utf8mb3_unicode_ci,
  `metaContainers` text COLLATE utf8mb3_unicode_ci,
  `redirectsContainer` text COLLATE utf8mb3_unicode_ci,
  `frontendTemplatesContainer` text COLLATE utf8mb3_unicode_ci,
  `metaBundleSettings` text COLLATE utf8mb3_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_sunquwvbkjpsuqpxlzvthxhsmoxkdlzeovvr` (`sourceBundleType`),
  KEY `idx_brpxjvqtqmwqeysgymwqelspcftnwprzqter` (`sourceId`),
  KEY `idx_rxjtqkgbtqugslrgxawlldoftgekynyugano` (`sourceSiteId`),
  KEY `idx_awwnrttlogaqlkxtmpxweinydsahjyhqzode` (`sourceHandle`),
  CONSTRAINT `fk_hsylhmiuowujlaadnqtgjsxexjdeuoqvhjvy` FOREIGN KEY (`sourceSiteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for sequences
-- ----------------------------
DROP TABLE IF EXISTS `sequences`;
CREATE TABLE `sequences` (
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `next` int unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for sessions
-- ----------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `token` char(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_sygfkgysumzurgehsuodpvtphfvzmvuyoqpt` (`uid`),
  KEY `idx_bkvrcgsxdsvphecktnrrqzhzfvlenahnnzzq` (`token`),
  KEY `idx_sxrazvlaaskmeecsmhnwccvaayibxhatrdrm` (`dateUpdated`),
  KEY `idx_qpnhubeunjgjgqqbwdvhxhnewmjibihcdzxg` (`userId`),
  CONSTRAINT `fk_mcurinnwythmrfpgltgwgtikcwaubsylwhjg` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=961 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for shunnedmessages
-- ----------------------------
DROP TABLE IF EXISTS `shunnedmessages`;
CREATE TABLE `shunnedmessages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `message` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `expiryDate` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_dkchbgopzpxnoqlazobcapoycgwrrckolwmz` (`userId`,`message`),
  CONSTRAINT `fk_wlzlosajlzfksbqxbfoaefdrgealvwtvqqdk` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for sitegroups
-- ----------------------------
DROP TABLE IF EXISTS `sitegroups`;
CREATE TABLE `sitegroups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `dateDeleted` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_pkiancgzjljkycnhmzfreomzjwegbgzeveye` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for sites
-- ----------------------------
DROP TABLE IF EXISTS `sites`;
CREATE TABLE `sites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `groupId` int NOT NULL,
  `primary` tinyint(1) NOT NULL,
  `enabled` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'true',
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `language` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `hasUrls` tinyint(1) NOT NULL DEFAULT '0',
  `baseUrl` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sortOrder` smallint unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `dateDeleted` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_zrxuthaufwwzejebiozbouavgfdqmdcronxa` (`dateDeleted`),
  KEY `idx_sprjuqapchvfbknagefhnfdizivrvfxcajzp` (`handle`),
  KEY `idx_iyelavoermikwqjhfmzbqnnzkjfuimlcyslg` (`sortOrder`),
  KEY `fk_dawgkgnrhkiyywylmmzuwhxvdwlfknsmyskh` (`groupId`),
  CONSTRAINT `fk_dawgkgnrhkiyywylmmzuwhxvdwlfknsmyskh` FOREIGN KEY (`groupId`) REFERENCES `sitegroups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for sso_identities
-- ----------------------------
DROP TABLE IF EXISTS `sso_identities`;
CREATE TABLE `sso_identities` (
  `provider` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `identityId` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `userId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  PRIMARY KEY (`provider`,`identityId`,`userId`),
  KEY `fk_hudcopwqanfnetfweddreikjsnnqqacjrxtz` (`userId`),
  CONSTRAINT `fk_hudcopwqanfnetfweddreikjsnnqqacjrxtz` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for structureelements
-- ----------------------------
DROP TABLE IF EXISTS `structureelements`;
CREATE TABLE `structureelements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `structureId` int NOT NULL,
  `elementId` int DEFAULT NULL,
  `root` int unsigned DEFAULT NULL,
  `lft` int unsigned NOT NULL,
  `rgt` int unsigned NOT NULL,
  `level` smallint unsigned NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_djkacgahriwrfmqaaobovyolrbamivwbbqwq` (`structureId`,`elementId`),
  KEY `idx_slkhkoiqjpgkhzcyhljxrskrnmjzocjshrpu` (`root`),
  KEY `idx_pjazyvdeqimwwhequjfmbyajmolrcrnunzfc` (`lft`),
  KEY `idx_pysckexjtmgfvyepolppmprlyyksjhudgyee` (`rgt`),
  KEY `idx_igkkmghpkzkirvjsjmktiengmtyllcptbulj` (`level`),
  KEY `idx_ltolmaxxsgakytmrfyfjcxqkqbnqotazpogy` (`elementId`),
  CONSTRAINT `fk_hdopjangjnqpbcbepgwozkqqbmbrdzdtghex` FOREIGN KEY (`structureId`) REFERENCES `structures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=111612 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for structures
-- ----------------------------
DROP TABLE IF EXISTS `structures`;
CREATE TABLE `structures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `maxLevels` smallint unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `dateDeleted` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_jrmnjuovdrrcnntrnjcuxayolcfufytnkwst` (`dateDeleted`)
) ENGINE=InnoDB AUTO_INCREMENT=7694 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for supertableblocks
-- ----------------------------
DROP TABLE IF EXISTS `supertableblocks`;
CREATE TABLE `supertableblocks` (
  `id` int NOT NULL,
  `ownerId` int NOT NULL,
  `fieldId` int NOT NULL,
  `typeId` int NOT NULL,
  `sortOrder` smallint unsigned DEFAULT NULL,
  `deletedWithOwner` tinyint(1) DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_jbasaspuqzpbmuloqspmsktrqinwryagrskp` (`ownerId`),
  KEY `idx_cmcrkyszbmyhuiojatekvpgadvqzpowqiyus` (`fieldId`),
  KEY `idx_abdquyegpgeagyozugtidwajgrowfgtqwnxt` (`typeId`),
  KEY `idx_whsvfmongklchzstzlibkmimwkghijqliwbf` (`sortOrder`),
  CONSTRAINT `fk_aalmksrparfqbnukmzupgocsamjnahdmieio` FOREIGN KEY (`ownerId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ptpxrlwmuevuygkqwgdshirtsdogdrlbhucm` FOREIGN KEY (`typeId`) REFERENCES `supertableblocktypes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_toxxzqgzwiqopxwssvruynwzufllonqpiosq` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_zlryyatyafjlegmzdtgcyekkssmaltnuwfts` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for supertableblocktypes
-- ----------------------------
DROP TABLE IF EXISTS `supertableblocktypes`;
CREATE TABLE `supertableblocktypes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fieldId` int NOT NULL,
  `fieldLayoutId` int DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_hwswfucshapkrlsmjihssycrxuqfbunitmsw` (`fieldId`),
  KEY `idx_eniuhzzxkzmiyzsvzlmazvashyqffxzwzvjk` (`fieldLayoutId`),
  CONSTRAINT `fk_kwbrgkxnbdnjgflkzvipsrsxgbjxwthmkjjs` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sukmwjjyumpkbmctpxjogcwzwhwlmwsqswpl` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for systemmessages
-- ----------------------------
DROP TABLE IF EXISTS `systemmessages`;
CREATE TABLE `systemmessages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `language` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `subject` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_rdysnoyxtapdbvheahinetkqggvlhzgslvaz` (`key`,`language`),
  KEY `idx_mhrakxwylexnevpebidpdkacykmmnsjoycvn` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for taggroups
-- ----------------------------
DROP TABLE IF EXISTS `taggroups`;
CREATE TABLE `taggroups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `fieldLayoutId` int DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `dateDeleted` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_qtcwfartvixhbgchqswtjinlcjwslxezpvvh` (`name`),
  KEY `idx_edjorzkflksrsmrbiezbqqpoelqcbtargbtf` (`handle`),
  KEY `idx_hhufzpazihchwufbszxhdazsmtzxtoqcwpsi` (`dateDeleted`),
  KEY `fk_ygimkristwmyiaqlxvptonwessojbvznmwue` (`fieldLayoutId`),
  CONSTRAINT `fk_ygimkristwmyiaqlxvptonwessojbvznmwue` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for tags
-- ----------------------------
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int NOT NULL,
  `groupId` int NOT NULL,
  `deletedWithGroup` tinyint(1) DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dzequqhmlfpkzdfplwpcjbzrggylvlixzhyq` (`groupId`),
  CONSTRAINT `fk_akjabgtlqfbjxrmfmmoxqycuafisjslnxfuw` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bddzkzlssjcdynyfycdchsbuoqjydfcsdcxw` FOREIGN KEY (`groupId`) REFERENCES `taggroups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for templatecacheelements
-- ----------------------------
DROP TABLE IF EXISTS `templatecacheelements`;
CREATE TABLE `templatecacheelements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cacheId` int NOT NULL,
  `elementId` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_uyzkvinrzpthclesuedvjwvyialuekkjkxat` (`cacheId`),
  KEY `idx_kbmrrvfzwkhqzhsdvfopsqsobevvafmqvogc` (`elementId`),
  CONSTRAINT `fk_kuenqoxwmngnjuasnvyslymymlsrimhvwtce` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vxidunaatvycplajqogosrmpnrkjekwedhxy` FOREIGN KEY (`cacheId`) REFERENCES `templatecaches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for templatecachequeries
-- ----------------------------
DROP TABLE IF EXISTS `templatecachequeries`;
CREATE TABLE `templatecachequeries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cacheId` int NOT NULL,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `query` longtext COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pncgzjcuwfnsxfzdtedaacdpayjkkpmvseto` (`cacheId`),
  KEY `idx_wyfrneeglkcyfuzihvlkuiurwqeqcniyopjp` (`type`),
  CONSTRAINT `fk_guxjbkibrnwveozehzickbydholyuyajxjck` FOREIGN KEY (`cacheId`) REFERENCES `templatecaches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for templatecaches
-- ----------------------------
DROP TABLE IF EXISTS `templatecaches`;
CREATE TABLE `templatecaches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `siteId` int NOT NULL,
  `cacheKey` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `expiryDate` datetime NOT NULL,
  `body` mediumtext COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_zjkxaebimqxbrrciazebqnnjrvhqhsidjrvp` (`cacheKey`,`siteId`,`expiryDate`,`path`),
  KEY `idx_erlbkehggrivbjvzidcwowztestucdxpojye` (`cacheKey`,`siteId`,`expiryDate`),
  KEY `idx_kwtrpgwwlzvnrgbtjnqxaidjbnkzmowdpmvx` (`siteId`),
  CONSTRAINT `fk_gagwrixjqowcczfkcwtmosjpuspnwkcbuqzn` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for tokens
-- ----------------------------
DROP TABLE IF EXISTS `tokens`;
CREATE TABLE `tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `token` char(32) COLLATE utf8mb3_unicode_ci NOT NULL,
  `route` text COLLATE utf8mb3_unicode_ci,
  `usageLimit` tinyint unsigned DEFAULT NULL,
  `usageCount` tinyint unsigned DEFAULT NULL,
  `expiryDate` datetime NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_wmpvfmjhofagatbypiscgjlatstqfqjirvhn` (`token`),
  KEY `idx_etuahseeadpwqwprgioymfcoleeczbrggdxx` (`expiryDate`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for usergroups
-- ----------------------------
DROP TABLE IF EXISTS `usergroups`;
CREATE TABLE `usergroups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb3_unicode_ci,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_skbvilhjauwyzfxledbckiaykxbdnsrywbov` (`handle`),
  KEY `idx_rjyvqbdbloymwzgioyceaxepbkgxwvxujsux` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for usergroups_users
-- ----------------------------
DROP TABLE IF EXISTS `usergroups_users`;
CREATE TABLE `usergroups_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `groupId` int NOT NULL,
  `userId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_lmdsocpoivezadxjreulfbmcdlmeqplgxvuw` (`groupId`,`userId`),
  KEY `idx_immhtwbgvkcskisviihdwptmbazcmkxiuvoh` (`userId`),
  CONSTRAINT `fk_egotguzaqvlubngjjuqfzmjwmdhlkwslahsh` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lsbetujzluhsnqqxiegnibbvanmbvqfsgidt` FOREIGN KEY (`groupId`) REFERENCES `usergroups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for userpermissions
-- ----------------------------
DROP TABLE IF EXISTS `userpermissions`;
CREATE TABLE `userpermissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_glncezoovdcucbozmvlenzxspdspfyirmwqa` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for userpermissions_usergroups
-- ----------------------------
DROP TABLE IF EXISTS `userpermissions_usergroups`;
CREATE TABLE `userpermissions_usergroups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `permissionId` int NOT NULL,
  `groupId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_xefyknqhltwhgxznwjkigrgejxwfbxrdtxbm` (`permissionId`,`groupId`),
  KEY `idx_omoxrixzxuzrwfmwicspbqecdyjsysqbjkgk` (`groupId`),
  CONSTRAINT `fk_cmxtydjwhiclmijbhdtmcuhqvbfcupzyrgay` FOREIGN KEY (`groupId`) REFERENCES `usergroups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ufwcdkinpewfzwdwhxbddedmmfnzjrjoyimw` FOREIGN KEY (`permissionId`) REFERENCES `userpermissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for userpermissions_users
-- ----------------------------
DROP TABLE IF EXISTS `userpermissions_users`;
CREATE TABLE `userpermissions_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `permissionId` int NOT NULL,
  `userId` int NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_lvgkxpndipogzptedbawajrsugntfozwqgeh` (`permissionId`,`userId`),
  KEY `idx_mvwginjhsrrxjtigheyyvdkkdaunphonfvvh` (`userId`),
  CONSTRAINT `fk_pdrqfhgsetvjoblelxxadsevatsnttmrdsvi` FOREIGN KEY (`permissionId`) REFERENCES `userpermissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_xtumpflupehavoipbdxmiqgkgtwhuiqesrso` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for userpreferences
-- ----------------------------
DROP TABLE IF EXISTS `userpreferences`;
CREATE TABLE `userpreferences` (
  `userId` int NOT NULL AUTO_INCREMENT,
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  PRIMARY KEY (`userId`),
  CONSTRAINT `fk_dqcyxwoqhiryqtrskblrocbzwddfcuyvkjkp` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `userpreferences_chk_1` CHECK (json_valid(`preferences`))
) ENGINE=InnoDB AUTO_INCREMENT=27597 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `fullName` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `photoId` int DEFAULT NULL,
  `affiliatedSiteId` int DEFAULT NULL,
  `firstName` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `lastName` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `suspended` tinyint(1) NOT NULL DEFAULT '0',
  `pending` tinyint(1) NOT NULL DEFAULT '0',
  `lastLoginDate` datetime DEFAULT NULL,
  `lastLoginAttemptIp` varchar(45) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `invalidLoginWindowStart` datetime DEFAULT NULL,
  `invalidLoginCount` tinyint unsigned DEFAULT NULL,
  `lastInvalidLoginDate` datetime DEFAULT NULL,
  `lockoutDate` datetime DEFAULT NULL,
  `hasDashboard` tinyint(1) NOT NULL DEFAULT '0',
  `verificationCode` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `verificationCodeIssuedDate` datetime DEFAULT NULL,
  `unverifiedEmail` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `passwordResetRequired` tinyint(1) NOT NULL DEFAULT '0',
  `lastPasswordChangeDate` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rmuiqwwaiwcgwxrxyimrszfaoujivxgqqrls` (`verificationCode`),
  KEY `idx_hwxaopdkbolismgabcacuxsvbmdhbpbtnnex` (`email`),
  KEY `idx_pvvwuuxfbbsgllbbdeoezirzqlnskobxkgxg` (`username`),
  KEY `fk_hlbzlulcrzopjjfkzaytnaohzkkntcrnwdcp` (`photoId`),
  KEY `idx_ubvkoejdgcwwyoawmpbkchgnfrphkjitcveq` (`active`),
  KEY `idx_japwbbpfmncqtnqiflroqvejbzpsgzqcoiip` (`locked`),
  KEY `idx_jmghyjcisvqnqlaytwdfcackcipzbilbnjfn` (`pending`),
  KEY `idx_kyugndadkgtapxmutvfhvsmwywqjsszuvnuv` (`suspended`),
  KEY `fk_evwrjppkupblpgobepfnggagzqgmecuqigvl` (`affiliatedSiteId`),
  CONSTRAINT `fk_evwrjppkupblpgobepfnggagzqgmecuqigvl` FOREIGN KEY (`affiliatedSiteId`) REFERENCES `sites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hlbzlulcrzopjjfkzaytnaohzkkntcrnwdcp` FOREIGN KEY (`photoId`) REFERENCES `assets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_yuvpmwlnctejykuzgzatccqxzdjbrylvjfnd` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for volumefolders
-- ----------------------------
DROP TABLE IF EXISTS `volumefolders`;
CREATE TABLE `volumefolders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parentId` int DEFAULT NULL,
  `volumeId` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_xafjbherigcbmlyoiifzgypqaxrfhdqcogsh` (`name`,`parentId`,`volumeId`),
  KEY `idx_uwdsskvzdxscfvnnayxsyqcyarkghyvolkok` (`parentId`),
  KEY `idx_soqadmwourjkshfgewjqjinzfovjdqwwyyuc` (`volumeId`),
  CONSTRAINT `fk_bdiboxwlotqakptryhbjejmcqfbgdmbgdozf` FOREIGN KEY (`parentId`) REFERENCES `volumefolders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fjryyiuereuucgnzbvzrfhntsrkvilcfkqvz` FOREIGN KEY (`volumeId`) REFERENCES `volumes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for volumes
-- ----------------------------
DROP TABLE IF EXISTS `volumes`;
CREATE TABLE `volumes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fieldLayoutId` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `handle` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `fs` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `subpath` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `transformFs` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `transformSubpath` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `titleTranslationMethod` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'site',
  `titleTranslationKeyFormat` text COLLATE utf8mb3_unicode_ci,
  `altTranslationMethod` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'site',
  `altTranslationKeyFormat` text COLLATE utf8mb3_unicode_ci,
  `sortOrder` smallint unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `dateDeleted` datetime DEFAULT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_mhfltetptfjahnldjfkvnvcxrfyjrkoanaog` (`name`),
  KEY `idx_dumjvkjlgjtougpxqddjvlmxjdozqsaztinl` (`handle`),
  KEY `idx_nghuktbhpcgjqndsuiqyrmzvweazvslyuhlw` (`fieldLayoutId`),
  KEY `idx_cwtlaaxvsbduopljraznwnncnjbfkllgoxer` (`dateDeleted`),
  CONSTRAINT `fk_mihbzksjptucvltqdrscrickogwvpapznadv` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for webauthn
-- ----------------------------
DROP TABLE IF EXISTS `webauthn`;
CREATE TABLE `webauthn` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `credentialId` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `credential` text COLLATE utf8mb3_unicode_ci,
  `credentialName` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dateLastUsed` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_wzebhbvltykgshsvmsbcquuakkpkszumhtkp` (`userId`),
  CONSTRAINT `fk_wzebhbvltykgshsvmsbcquuakkpkszumhtkp` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for widgets
-- ----------------------------
DROP TABLE IF EXISTS `widgets`;
CREATE TABLE `widgets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `sortOrder` smallint unsigned DEFAULT NULL,
  `colspan` tinyint DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_vbhpybbxtnqwdikttfatvsfjeevwryxrniem` (`userId`),
  CONSTRAINT `fk_xobjvbtjfbjewjrczmilgoizauzizjugpifw` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `widgets_chk_1` CHECK (json_valid(`settings`))
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
