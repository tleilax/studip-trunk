#
# Dumping data for table 'resources_categories'
#

INSERT INTO `resources_categories` VALUES ('3cbcc99c39476b8e2c8eef5381687461', 'Geb�ude', '', 0, 0, 1);
INSERT INTO `resources_categories` VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', 'H�rsa�le', '', 0, 1, 1);
INSERT INTO `resources_categories` VALUES ('f3351baeca8776d4ffe4b672f568cbed', 'Ger�te', '', 0, 0, 1);
INSERT INTO `resources_categories` VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '�bungsr�ume', '', 0, 1, 1);

#
# Dumping data for table 'resources_categories_properties'
#

INSERT INTO `resources_categories_properties` VALUES ('3cbcc99c39476b8e2c8eef5381687461', 'c4f13691419a6c12d38ad83daa926c7c', 0, 0);
INSERT INTO `resources_categories_properties` VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', 'afb8675e2257c03098aa34b2893ba686', 1, 0);
INSERT INTO `resources_categories_properties` VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', '7c1a8f6001cfdcb9e9c33eeee0ef343d', 1, 0);
INSERT INTO `resources_categories_properties` VALUES ('3cbcc99c39476b8e2c8eef5381687461', 'b79b77f40706ed598f5403f953c1f791', 0, 0);
INSERT INTO `resources_categories_properties` VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', '1f8cef2b614382e36eaa4a29f6027edf', 1, 0);
INSERT INTO `resources_categories_properties` VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', '44fd30e8811d0d962582fa1a9c452bdd', 1, 0);
INSERT INTO `resources_categories_properties` VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', '613cfdf6aa1072e21a1edfcfb0445c69', 1, 0);
INSERT INTO `resources_categories_properties` VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', '28addfe18e86cc3587205734c8bc2372', 1, 0);
INSERT INTO `resources_categories_properties` VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '7c1a8f6001cfdcb9e9c33eeee0ef343d', 1, 0);
INSERT INTO `resources_categories_properties` VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', 'afb8675e2257c03098aa34b2893ba686', 1, 0);
INSERT INTO `resources_categories_properties` VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', 'b79b77f40706ed598f5403f953c1f791', 1, 0);
INSERT INTO `resources_categories_properties` VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '1f8cef2b614382e36eaa4a29f6027edf', 1, 0);
INSERT INTO `resources_categories_properties` VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '44fd30e8811d0d962582fa1a9c452bdd', 1, 0);
INSERT INTO `resources_categories_properties` VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '613cfdf6aa1072e21a1edfcfb0445c69', 1, 0);
INSERT INTO `resources_categories_properties` VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '28addfe18e86cc3587205734c8bc2372', 1, 0);
INSERT INTO `resources_categories_properties` VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', 'b79b77f40706ed598f5403f953c1f791', 1, 0);
INSERT INTO `resources_categories_properties` VALUES ('f3351baeca8776d4ffe4b672f568cbed', 'cb8140efbc2af5362b1159c65deeec9e', 0, 0);
INSERT INTO `resources_categories_properties` VALUES ('f3351baeca8776d4ffe4b672f568cbed', 'c4352a580051a81830ef5980941c9e06', 0, 0);
INSERT INTO `resources_categories_properties` VALUES ('f3351baeca8776d4ffe4b672f568cbed', '39c73942e1c1650fa20c7259be96b3f3', 0, 0);

#
# Dumping data for table 'resources_properties'
#

INSERT INTO `resources_properties` VALUES ('44fd30e8811d0d962582fa1a9c452bdd', 'Sitzpl�tze', '', 'num', '', 2);
INSERT INTO `resources_properties` VALUES ('c4f13691419a6c12d38ad83daa926c7c', 'Adresse', '', 'text', '', 0);
INSERT INTO `resources_properties` VALUES ('7c1a8f6001cfdcb9e9c33eeee0ef343d', 'Beamer', '', 'bool', 'vorhanden', 0);
INSERT INTO `resources_properties` VALUES ('b79b77f40706ed598f5403f953c1f791', 'behindertengerecht', '', 'bool', 'vorhanden', 0);
INSERT INTO `resources_properties` VALUES ('613cfdf6aa1072e21a1edfcfb0445c69', 'Tageslichtprojektor', '', 'bool', 'vorhanden', 0);
INSERT INTO `resources_properties` VALUES ('afb8675e2257c03098aa34b2893ba686', 'Dozentenrechner', '', 'bool', 'vorhanden', 0);
INSERT INTO `resources_properties` VALUES ('1f8cef2b614382e36eaa4a29f6027edf', 'Audio-Anlage', '', 'bool', 'vorhanden', 0);
INSERT INTO `resources_properties` VALUES ('c4352a580051a81830ef5980941c9e06', 'Seriennummer', '', 'num', '', 0);
INSERT INTO `resources_properties` VALUES ('cb8140efbc2af5362b1159c65deeec9e', 'Hersteller', '', 'select', 'Sony;Philips;Technics;Telefunken;anderer', 0);
INSERT INTO `resources_properties` VALUES ('39c73942e1c1650fa20c7259be96b3f3', 'Inventarnummer', '', 'num', '', 0);
INSERT INTO `resources_properties` VALUES ('28addfe18e86cc3587205734c8bc2372', 'Verdunklung', '', 'bool', 'vorhanden', 0);