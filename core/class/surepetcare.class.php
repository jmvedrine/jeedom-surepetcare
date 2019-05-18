<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class surepetcare extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */
    public static function request($url, $payload = null, $method = 'POST', $headers = array()) {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $requestHeaders = array(
            'Connection: keep-alive',
            'Origin: https://surepetcare.io',
            'Referer: https://surepetcare.io/',
        );

        if($method == 'POST' || $method == 'PUT') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $requestHeaders[] = 'Content-Type: application/json';
            $requestHeaders[] = 'Content-Length: ' . strlen($json);
        }

        if(count($headers) > 0) {
            $requestHeaders = array_merge($requestHeaders, $headers);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; Android 7.0; SM-G930F Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/64.0.3282.137 Mobile Safari/537.36');

        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code =='200') {
            return json_decode($result, true);
        } else {
            throw new \Exception(__('Erreur lors de la requete : ',__FILE__).$url.'('.$method.'), data : '.json_encode($payload).' erreur : ' . $code);
        }
    }

    public static function breedname($breedid){
        switch ($breedid) {
            case 1:
                return "Affenpinscher";
                break;
            case 2:
                return "Lévrier afghan";
                break;
            case 3:
                return "Lycaon";
                break;
            case 4:
                return "Africanis";
                break;
            case 5:
                return "Aïdi";
                break;
            case 6:
                return "Airedale terrier";
                break;
            case 7:
                return "Akbash";
                break;
            case 8:
                return "Alano Espanol";
                break;
            case 9:
                return "Alapaha Blue Blood Bulldog";
                break;
            case 10:
                return "Alaskan Husky";
                break;
            case 11:
                return "Alaskan Klee Kai";
                break;
            case 12:
                return "Malamute de l’Alaska";
                break;
            case 13:
                return "Alopekis";
                break;
            case 14:
                return "Akita américain";
                break;
            case 15:
                return "Bulldog américain";
                break;
            case 16:
                return "Cocker américain";
                break;
            case 17:
                return "Esquimau américain";
                break;
            case 18:
                return "Foxhound américain";
                break;
            case 19:
                return "American Hairless terrier";
                break;
            case 20:
                return "Mastiff américain";
                break;
            case 21:
                return "American Pit Bull Terrier";
                break;
            case 22:
                return "American Staffordshire terrier";
                break;
            case 23:
                return "American Staghound";
                break;
            case 24:
                return "Chien d’eau américain";
                break;
            case 25:
                return "Berger Blanc américain";
                break;
            case 26:
                return "Berger d’Anatolie";
                break;
            case 27:
                return "Chien andalou";
                break;
            case 28:
                return "Bouvier de l’Appenzell";
                break;
            case 29:
                return "Dogue argentin";
                break;
            case 30:
                return "Braque de l’Ariège";
                break;
            case 31:
                return "Ariégeois";
                break;
            case 32:
                return "Australian Bulldog";
                break;
            case 33:
                return "Bouvier australien";
                break;
            case 34:
                return "Australian Kelpie";
                break;
            case 35:
                return "Berger australien";
                break;
            case 36:
                return "Terrier australien à poil soyeux";
                break;
            case 37:
                return "Bouvier australien courte queue";
                break;
            case 38:
                return "Terrier australien";
                break;
            case 39:
                return "Brachet noir et feu";
                break;
            case 40:
                return "Pinscher autrichien";
                break;
            case 41:
                return "Azawakh";
                break;
            case 42:
                return "Barbet";
                break;
            case 43:
                return "Basenji";
                break;
            case 44:
                return "Basset artésien normand";
                break;
            case 45:
                return "Basset bleu de Gascogne";
                break;
            case 46:
                return "Basset fauve de Bretagne";
                break;
            case 47:
                return "Basset hound";
                break;
            case 48:
                return "Chien rouge de Bavière";
                break;
            case 49:
                return "Beagle";
                break;
            case 50:
                return "Beagle-Harrier";
                break;
            case 51:
                return "Beaglier";
                break;
            case 52:
                return "Bearded collie";
                break;
            case 53:
                return "Berger de Beauce";
                break;
            case 54:
                return "Bedlington terrier";
                break;
            case 55:
                return "Berger belge Laekenois";
                break;
            case 56:
                return "Malinois";
                break;
            case 57:
                return "Berger belge Tervuren";
                break;
            case 58:
                return "Berger de Bergame";
                break;
            case 59:
                return "Berger picard";
                break;
            case 60:
                return "Bouvier bernois";
                break;
            case 61:
                return "Bichon frisé";
                break;
            case 62:
                return "Chien noir et feu pour la chasse au raton laveur";
                break;
            case 63:
                return "Chien d’élan norvégien noir";
                break;
            case 64:
                return "Terrier noir russe";
                break;
            case 65:
                return "Black Mouth Cur";
                break;
            case 66:
                return "Chien de Saint-Hubert";
                break;
            case 67:
                return "Blue Lacy";
                break;
            case 68:
                return "Épagneul bleu de Picardie";
                break;
            case 69:
                return "Bluetick coonhound";
                break;
            case 70:
                return "Boerboel";
                break;
            case 71:
                return "Berger de Bohême";
                break;
            case 72:
                return "Bichon bolonais";
                break;
            case 73:
                return "Border collie";
                break;
            case 74:
                return "Border terrier";
                break;
            case 75:
                return "Barzoï";
                break;
            case 76:
                return "Terrier de Boston";
                break;
            case 77:
                return "Bouvier des Ardennes";
                break;
            case 78:
                return "Bouvier des Flandres";
                break;
            case 79:
                return "Boxer";
                break;
            case 80:
                return "Boykin Spaniel";
                break;
            case 81:
                return "Braque italien";
                break;
            case 82:
                return "Braque du Bourbonnais";
                break;
            case 83:
                return "Terrier brésilien";
                break;
            case 84:
                return "Briard";
                break;
            case 85:
                return "Briquet griffon vendéen";
                break;
            case 86:
                return "Griffon bruxellois";
                break;
            case 87:
                return "Berger roumain de Bucovine";
                break;
            case 88:
                return "Berger bulgare";
                break;
            case 89:
                return "Bull terrier";
                break;
            case 90:
                return "Bouledogue";
                break;
            case 91:
                return "Bullmastiff";
                break;
            case 92:
                return "Berger de la Serra de Aires";
                break;
            case 93:
                return "Cairn terrier";
                break;
            case 94:
                return "Chien de Canaan";
                break;
            case 95:
                return "Esquimau canadien";
                break;
            case 96:
                return "Cane Corso";
                break;
            case 97:
                return "Caravan hound";
                break;
            case 98:
                return "Welsh Corgi Cardigan";
                break;
            case 99:
                return "Dingo Américain";
                break;
            case 100:
                return "Chien de berger roumain des Carpathes";
                break;
            case 101:
                return "Catahoula Bulldog";
                break;
            case 102:
                return "Catahoula Cur";
                break;
            case 103:
                return "Chien léopard catahoula";
                break;
            case 104:
                return "Gos d’Atura Català";
                break;
            case 105:
                return "Berger du Caucase";
                break;
            case 106:
                return "Cavalier King Charles Spaniel";
                break;
            case 107:
                return "Berger d’Asie Centrale";
                break;
            case 108:
                return "Barbu tchèque";
                break;
            case 109:
                return "Terrier Tchèque";
                break;
            case 110:
                return "Lévrier Polonais ";
                break;
            case 111:
                return "Retriever de la baie de Chesapeake";
                break;
            case 112:
                return "Chihuahua";
                break;
            case 113:
                return "Chien chinois à crête";
                break;
            case 114:
                return "Chow-chow";
                break;
            case 115:
                return "Cirneco de l’Etna";
                break;
            case 116:
                return "Clumber Spaniel";
                break;
            case 117:
                return "Cockapoo";
                break;
            case 118:
                return "Coton de Tuléar";
                break;
            case 119:
                return "Berger croate";
                break;
            case 120:
                return "Retriever à poil bouclé";
                break;
            case 121:
                return "Chien-loup tchécoslovaque";
                break;
            case 122:
                return "Teckel";
                break;
            case 123:
                return "Dalmatien";
                break;
            case 124:
                return "Dandie Dinmont Terrier";
                break;
            case 125:
                return "Chien de ferme dano-suédois";
                break;
            case 126:
                return "Brachet allemand";
                break;
            case 127:
                return "Dingo";
                break;
            case 128:
                return "Dobermann";
                break;
            case 129:
                return "Dogue de Bordeaux";
                break;
            case 130:
                return "Chien de perdrix de Drente";
                break;
            case 131:
                return "Basset suédois";
                break;
            case 132:
                return "Dunker";
                break;
            case 133:
                return "Berger hollandais";
                break;
            case 134:
                return "Smous des Pays-Bas";
                break;
            case 135:
                return "Laïka de Sibérie orientale";
                break;
            case 136:
                return "Berger d’Europe de l’est";
                break;
            case 137:
                return "English Coonhound";
                break;
            case 138:
                return "Foxhound anglais";
                break;
            case 139:
                return "Mastiff anglais";
                break;
            case 140:
                return "Setter anglais";
                break;
            case 141:
                return "Berger anglais";
                break;
            case 142:
                return "Springer anglais";
                break;
            case 143:
                return "Bouvier de l’Entlebuch";
                break;
            case 144:
                return "Eurasier";
                break;
            case 145:
                return "Field Spaniel";
                break;
            case 146:
                return "Fila Brasileiro";
                break;
            case 147:
                return "Chien courant finlandais";
                break;
            case 148:
                return "Chien finnois de Laponie";
                break;
            case 149:
                return "Spitz finlandais";
                break;
            case 150:
                return "Retriever à poil plat";
                break;
            case 151:
                return "Français blanc et noir";
                break;
            case 152:
                return "Épagneul breton";
                break;
            case 153:
                return "Bouledogue français";
                break;
            case 154:
                return "Épagneul français";
                break;
            case 155:
                return "Chien d’arrêt allemand à poil long";
                break;
            case 156:
                return "Pinscher allemand";
                break;
            case 157:
                return "Berger allemand";
                break;
            case 158:
                return "Braque allemand à poil court";
                break;
            case 159:
                return "Chien d’arrêt allemand à poil dur";
                break;
            case 160:
                return "Schnauzer géant";
                break;
            case 161:
                return "Terrier irlandais Glen of Imaal";
                break;
            case 162:
                return "Golden retriever";
                break;
            case 163:
                return "Goldendoodle";
                break;
            case 164:
                return "Setter Gordon";
                break;
            case 165:
                return "Grand basset griffon vendéen";
                break;
            case 166:
                return "Dogue allemand";
                break;
            case 167:
                return "Chien de montagne des Pyrénées";
                break;
            case 168:
                return "Grand bouvier suisse";
                break;
            case 169:
                return "Chien du Groenland";
                break;
            case 170:
                return "Greyhound";
                break;
            case 171:
                return "Griffon bleu de Gascogne";
                break;
            case 172:
                return "Berger belge Groenendael";
                break;
            case 173:
                return "Chien courant de Hamilton";
                break;
            case 174:
                return "Harrier";
                break;
            case 175:
                return "Bichon havanais";
                break;
            case 176:
                return "Hokkaïdo ";
                break;
            case 177:
                return "Hovawart";
                break;
            case 178:
                return "Podenco d’Ibiza";
                break;
            case 179:
                return "Chien de berger islandais";
                break;
            case 180:
                return "Setter irlandais rouge et blanc";
                break;
            case 181:
                return "Setter irlandais";
                break;
            case 182:
                return "Terrier irlandais";
                break;
            case 183:
                return "Épagneul d’eau irlandais";
                break;
            case 184:
                return "Irish wolfhound";
                break;
            case 185:
                return "Petit lévrier italien";
                break;
            case 186:
                return "Jack Russell terrier";
                break;
            case 187:
                return "Terrier de chasse allemand";
                break;
            case 188:
                return "Chien d’élan suédois (jämthund)";
                break;
            case 189:
                return "Épagneul japonais";
                break;
            case 190:
                return "Spitz japonais";
                break;
            case 191:
                return "Terrier japonais";
                break;
            case 192:
                return "Chien d’ours de Carélie";
                break;
            case 193:
                return "Spitz Loup";
                break;
            case 194:
                return "Terrier Kerry Blue";
                break;
            case 195:
                return "Kishu";
                break;
            case 196:
                return "Komondor";
                break;
            case 197:
                return "Kooikerhondje";
                break;
            case 198:
                return "Koolie australien";
                break;
            case 199:
                return "Jindo coréen";
                break;
            case 200:
                return "Kromfohrländer";
                break;
            case 201:
                return "Kuvasz";
                break;
            case 202:
                return "Labrador retriever";
                break;
            case 203:
                return "Chien d’eau romagnol";
                break;
            case 204:
                return "Lakeland Terrier";
                break;
            case 205:
                return "Lancashire Heeler";
                break;
            case 206:
                return "Landseer";
                break;
            case 207:
                return "Berger finnois de Laponie";
                break;
            case 208:
                return "Grand épagneul de Münster";
                break;
            case 209:
                return "Leonberg ";
                break;
            case 210:
                return "Lhassa Apso";
                break;
            case 211:
                return "Petit chien lion";
                break;
            case 212:
                return "Bichon maltais";
                break;
            case 213:
                return "Manchester terrier";
                break;
            case 214:
                return "McNab";
                break;
            case 215:
                return "Bull terrier miniature";
                break;
            case 216:
                return "Pinscher nain";
                break;
            case 217:
                return "Schnauzer miniature";
                break;
            case 218:
                return "Mudi";
                break;
            case 219:
                return "Mâtin napolitain";
                break;
            case 220:
                return "Chien chanteur de Nouvelle-Guinée";
                break;
            case 221:
                return "Terre-neuve";
                break;
            case 222:
                return "Norfolk Terrier";
                break;
            case 223:
                return "Spitz de Norrbotten";
                break;
            case 224:
                return "Inuit du Nord";
                break;
            case 225:
                return "Buhund norvégien";
                break;
            case 226:
                return "Chien d’élan norvégien gris";
                break;
            case 227:
                return "Chien norvégien de macareux";
                break;
            case 228:
                return "Norwich Terrier";
                break;
            case 229:
                return "Bobtail";
                break;
            case 230:
                return "Olde English Bulldogge";
                break;
            case 231:
                return "Chien à loutre";
                break;
            case 232:
                return "Alangu Mastiff";
                break;
            case 233:
                return "Épagneul nain continental papillon";
                break;
            case 234:
                return "Parson Russell Terrier";
                break;
            case 235:
                return "Patterdale Terrier";
                break;
            case 236:
                return "Pékinois";
                break;
            case 237:
                return "Welsh Corgi Pembroke";
                break;
            case 238:
                return "Dogue des Canaries";
                break;
            case 239:
                return "Dogue Majorquin";
                break;
            case 240:
                return "Chien nu du Pérou";
                break;
            case 241:
                return "Petit basset griffon vendéen";
                break;
            case 242:
                return "Chien du pharaon";
                break;
            case 243:
                return "Épagneul picard";
                break;
            case 244:
                return "Plott Hound";
                break;
            case 245:
                return "Plummer Terrier";
                break;
            case 246:
                return "Chien courant polonais";
                break;
            case 247:
                return "Berger polonais de plaine";
                break;
            case 248:
                return "Spitz nain";
                break;
            case 249:
                return "Épagneul de Pont-Audemer";
                break;
            case 250:
                return "Caniche";
                break;
            case 251:
                return "Chien de garenne portugais";
                break;
            case 252:
                return "Chien d’arrêt portugais";
                break;
            case 253:
                return "Chien d’eau portugais";
                break;
            case 254:
                return "Ratier de Prague";
                break;
            case 255:
                return "Pudelpointer";
                break;
            case 256:
                return "Carlin";
                break;
            case 257:
                return "Puli";
                break;
            case 258:
                return "Pumi";
                break;
            case 259:
                return "Berger des Pyrénées";
                break;
            case 260:
                return "Rafeiro de l’Alentejo";
                break;
            case 261:
                return "Rajapalayam";
                break;
            case 262:
                return "Rat Terrier";
                break;
            case 263:
                return "Redbone Coonhound";
                break;
            case 264:
                return "Chien de Rhodésie à crête dorsale";
                break;
            case 265:
                return "Rottweiler";
                break;
            case 266:
                return "Colley à poil long";
                break;
            case 267:
                return "Épagneul russe";
                break;
            case 268:
                return "Petit chien russe";
                break;
            case 269:
                return "Chien-loup de Saarloos";
                break;
            case 270:
                return "Lévrier persan";
                break;
            case 271:
                return "Samoyède";
                break;
            case 272:
                return "Sapsali";
                break;
            case 273:
                return "Chien de berger yougoslave de Charplanina";
                break;
            case 274:
                return "Schapendoes néerlandais";
                break;
            case 275:
                return "Schipperke";
                break;
            case 276:
                return "Lévrier écossais";
                break;
            case 277:
                return "Terrier écossais";
                break;
            case 278:
                return "Sealyham Terrier";
                break;
            case 279:
                return "Seppala Siberian Sleddog";
                break;
            case 280:
                return "Chien courant serbe";
                break;
            case 281:
                return "Shar-Pei";
                break;
            case 282:
                return "Berger des Shetland";
                break;
            case 283:
                return "Shiba";
                break;
            case 284:
                return "Shih Tzu";
                break;
            case 285:
                return "Shikoku";
                break;
            case 286:
                return "Shiloh Shepherd Dog";
                break;
            case 287:
                return "Husky de Sibérie";
                break;
            case 288:
                return "Lévrier de soie";
                break;
            case 289:
                return "Skye Terrier";
                break;
            case 290:
                return "Lévrier arabe";
                break;
            case 291:
                return "Chien courant du Småland";
                break;
            case 292:
                return "Petit épagneul de Münster";
                break;
            case 293:
                return "Colley à poil court";
                break;
            case 294:
                return "Fox-terrier à poil lisse";
                break;
            case 295:
                return "Terrier irlandais à poil doux";
                break;
            case 296:
                return "Berger de Russie méridionale";
                break;
            case 297:
                return "Mâtin espagnol";
                break;
            case 298:
                return "Chien d’eau espagnol";
                break;
            case 299:
                return "Spinone";
                break;
            case 300:
                return "Saint-Bernard";
                break;
            case 301:
                return "Staffordshire Bull Terrier";
                break;
            case 302:
                return "Schnauzer moyen";
                break;
            case 303:
                return "Sussex Spaniel";
                break;
            case 304:
                return "Lapphund suédois";
                break;
            case 305:
                return "Vallhund suédois";
                break;
            case 306:
                return "Tamaskan";
                break;
            case 307:
                return "Bangkaew de Thaïlande";
                break;
            case 308:
                return "Chien thaïlandais à crête dorsale";
                break;
            case 309:
                return "Dogue du Tibet";
                break;
            case 310:
                return "Épagneul tibétain";
                break;
            case 311:
                return "Terrier tibétain";
                break;
            case 312:
                return "Tolling Retriever";
                break;
            case 313:
                return "Berger de Bosnie-Herzégovine et de Croatie";
                break;
            case 314:
                return "Tosa";
                break;
            case 315:
                return "Toy Bulldog";
                break;
            case 316:
                return "Toy Fox Terrier";
                break;
            case 317:
                return "Toy Manchester Terrier";
                break;
            case 318:
                return "Chien courant de Transylvanie";
                break;
            case 319:
                return "Treeing Walker Coonhound";
                break;
            case 320:
                return "Braque hongrois à poil court";
                break;
            case 321:
                return "Volpino italien";
                break;
            case 322:
                return "Braque de Weimar";
                break;
            case 323:
                return "Welsh Springer Spaniel";
                break;
            case 324:
                return "Welsh Terrier";
                break;
            case 325:
                return "West Highland White Terrier";
                break;
            case 326:
                return "Laïka de Sibérie occidentale";
                break;
            case 327:
                return "Chien d’eau frison";
                break;
            case 328:
                return "Lévrier whippet";
                break;
            case 329:
                return "Fox-terrier à poil dur";
                break;
            case 330:
                return "Griffon d’arrêt à poil dur Korthals";
                break;
            case 331:
                return "Xoloitzcuintle";
                break;
            case 332:
                return "Yorkshire terrier";
                break;
            case 385:
                return "";
                break;
            case 333:
                return "Abyssin";
                break;
            case 334:
                return "Bobtail américain";
                break;
            case 335:
                return "American Curl";
                break;
            case 336:
                return "American Shorthair";
                break;
            case 337:
                return "American Wirehair";
                break;
            case 338:
                return "Balinais";
                break;
            case 339:
                return "Bengal";
                break;
            case 340:
                return "Sacré de Birmanie";
                break;
            case 341:
                return "Bombay";
                break;
            case 342:
                return "British Shorthair";
                break;
            case 343:
                return "Birman";
                break;
            case 344:
                return "Burmilla";
                break;
            case 345:
                return "Chartreux";
                break;
            case 346:
                return "Li Hua chinois";
                break;
            case 347:
                return "Colorpoint Shorthair";
                break;
            case 348:
                return "Cornish Rex";
                break;
            case 349:
                return "Cymric";
                break;
            case 350:
                return "Devon Rex";
                break;
            case 351:
                return "Mau égyptien";
                break;
            case 352:
                return "Burmese européen";
                break;
            case 353:
                return "Exotique";
                break;
            case 354:
                return "Havana Brown";
                break;
            case 355:
                return "Himalayen";
                break;
            case 356:
                return "Bobtail japonais";
                break;
            case 357:
                return "Javanais";
                break;
            case 358:
                return "Korat";
                break;
            case 359:
                return "LaPerm";
                break;
            case 360:
                return "Maine Coon";
                break;
            case 361:
                return "Manx";
                break;
            case 362:
                return "Nebelung";
                break;
            case 363:
                return "Norvégien";
                break;
            case 364:
                return "Ocicat";
                break;
            case 365:
                return "Oriental";
                break;
            case 366:
                return "Persan";
                break;
            case 367:
                return "Ragamuffin";
                break;
            case 368:
                return "Ragdoll";
                break;
            case 369:
                return "Bleu russe";
                break;
            case 370:
                return "Savannah";
                break;
            case 371:
                return "Scottish Fold";
                break;
            case 372:
                return "Selkirk Rex";
                break;
            case 373:
                return "Chat siamois";
                break;
            case 374:
                return "Sibérien";
                break;
            case 375:
                return "Singapura";
                break;
            case 376:
                return "Snowshoe";
                break;
            case 377:
                return "Somali";
                break;
            case 378:
                return "Sphynx";
                break;
            case 379:
                return "Tonkinois";
                break;
            case 380:
                return "Angora Turc";
                break;
            case 381:
                return "Turc de Van";
                break;
            case 382:
                return "Chat à poil court";
                break;
            case 384:
                return "Chat de gouttière";
                break;
            case 383:
                return "Chat à poil long";
            default:
                return "Race inconnue";
        }
    }

  public static function login() {
    $url = 'https://app.api.surehub.io/api/auth/login';
    $mailadress = config::byKey('emailAdress','surepetcare');
    $password = config::byKey('password','surepetcare');
    $device_id = rand(1,9);
    for($i=0; $i<9; $i++) {
        $device_id .= rand(0,9);
    }

    log::add('surepetcare','debug', 'device id='.$device_id);
    $data = array(
            'email_address' => $mailadress,
            'password' => $password,
            'device_id' => $device_id
    );
    $json = json_encode($data);
    log::add('surepetcare','debug', 'login data='.$json);
    $request_http = new com_http($url);
    $request_http->setNoSslCheck(true);
    $request_http->setUserAgent('Mozilla/5.0 (Linux; Android 7.0; SM-G930F Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/64.0.3282.137 Mobile Safari/537.36');
    $headers = array(
            'Connection: keep-alive',
            'Origin: https://surepetcare.io',
            'Referer: https://surepetcare.io/',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json)
    );
    $request_http->setHeader($headers);
    $request_http->setPost(json_encode($data));

    $result = $request_http->exec();
    log::add('surepetcare','debug','login result='.$result);
    $result = is_json($result, $result);
    if(isset($result['data']['token'])) {
            $token = $result['data']['token'];
            $userid = $result['data']['user']['id'];
            return $token;
    }
        return false;
  }

  public static function getHouseholds($token){
    $url = 'https://app.api.surehub.io/api/household';
    $request_http = new com_http($url);
    $request_http->setNoSslCheck(true);
    $requestHeaders = array(
            'Connection: keep-alive',
            'Origin: https://surepetcare.io',
            'Referer: https://surepetcare.io/',
            'Authorization: Bearer ' . $token
        );
    $request_http->setHeader($requestHeaders);
    $request_http->setUserAgent('Mozilla/5.0 (Linux; Android 7.0; SM-G930F Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/64.0.3282.137 Mobile Safari/537.36');

    $result = $request_http->exec();
    log::add('surepetcare','debug','Gethouseholds result : '.$result);
    $result = is_json($result, $result);
    $households = $result['data'];
    if(count($households) == 0){
      return;
    }
    log::add('surepetcare','debug','Found '.count($households). ' households');
    // config::remove('households', 'surepetcare');
    $households_config = config::byKey('households','surepetcare',array());
    log::add('surepetcare','debug','Household_config= '.print_r($households_config, true));
    foreach ($households as $household) {
      log::add('surepetcare','debug','Household id= '.$household['id']);
      log::add('surepetcare','debug','Household name= '.$household['name']);
      foreach ($households_config as $key=>$household_config) {
        if($household_config['id'] == $household['id']){
          $household_config['name'] = $household['name'];
          $households_config[$key] = $household_config;
          break(2);
        }
      }
      $households_config[] = array('id' => $household['id'], 'name' => $household['name']);
    }
    config::save('households',$households_config,'surepetcare');
  }
  public static function sync(){
    log::add('surepetcare', 'debug', 'Fonction sync appelee');
    $token = surepetcare::login();
    log::add('surepetcare','debug','dans login token='.$token);
    surepetcare::getHouseholds($token);
    $households = config::byKey('households','surepetcare',array());
    foreach ($households as $household) {
        // Récupération des devices.
        $result = surepetcare::request('https://app.api.surehub.io/api/household/'. $household['id'].'/device', null, 'GET', array('Authorization: Bearer ' . $token));
        log::add('surepetcare','debug','getDevices result : '.json_encode($result));

        if(isset($result['data'])) {
            $devices = $result['data'];
            foreach ($devices as $key => $device) {
                log::add('surepetcare','debug','Device '.$key. '='.json_encode($device));
                if(!isset($device['id']) || !isset($device['product_id'])){
                    log::add('surepetcare','debug','Missing device id or product id');
                    continue;
                }
                $found_eqLogics[] = self::findProduct($device,$household['id'], $household['name']);
                log::add('surepetcare','debug',json_encode($found_eqLogics));
            }
        }
        $result = surepetcare::request('https://app.api.surehub.io/api/household/'. $household['id'].'/pet?with[]=photo&with[]=tag&with[]=position', null, 'GET', array('Authorization: Bearer ' . $token));
        log::add('surepetcare','debug','getPets result : '.json_encode($result));
        
        if(isset($result['data'])) {
            foreach($result['data'] as $key => $pet){
                log::add('surepetcare','debug','Pet '.$key. '='.json_encode($pet));
                log::add('surepetcare','debug','Photo:'.$pet['photo']['location']);
                if(!isset($pet['id']) || !isset($pet['name'])){
                    log::add('surepetcare','debug','Missing pet id or name');
                    continue;
                }
                $found_eqLogics[] = self::findPet($pet,$household['id'], $household['name']);
                log::add('surepetcare','debug',json_encode($found_eqLogics));
            }
        }
    }

  }

  public function applyData($_data) {
    $updatedValue = false;
    if(!isset($_data['uniqueid'])){
      return $updatedValue;
    }

    $deviceIdList = explode('-', $_data['uniqueid'],2);
    foreach ($this->getCmd('info') as $cmd) {
      $logicalId = $cmd->getLogicalId();
      if ($logicalId == '') {
        continue;
      }
      $epClusterPath = explode('.', $logicalId);
      if ($epClusterPath[0] != $deviceIdList[1]) {
        continue;
      }
      $path = explode('::', $epClusterPath[1]);
      $value = $_data;
      foreach ($path as $key) {
        if (!isset($value[$key])) {
          continue (2);
        }
        $value = $value[$key];
      }
      if (!is_array($value)){
        $this->checkAndUpdateCmd($cmd,$value);
        $updatedValue = true;
      }
    }
    if(isset($_data['config'])) {
      $updatedValue = true;
      if ( isset($_data['config']['battery'])){
        $this->batteryStatus($_data['config']['battery']);
      }
    }
    return $updatedValue;
  }
  public static function findProduct($_device,$_householdid, $_householdname) {
    $create = false;
    $eqLogic = self::byLogicalId($_device['id'], 'surepetcare');
    if(!is_object($eqLogic)){
       log::add('surepetcare','debug','new device '.$_device['id']);
      event::add('jeedom::alert', array(
        'level' => 'warning',
        'page' => 'surepetcare',
        'message' => __('Nouveau produit detecté', __FILE__),
      ));
      $create = true;
      $eqLogic = new surepetcare();
      $eqLogic->setName($_device['name']);
    }
    $eqLogic->setEqType_name('surepetcare');
    $eqLogic->setIsEnable(1);
    $eqLogic->setLogicalId($_device['id']);
    $eqLogic->setConfiguration('household_id', $_householdid);
    $eqLogic->setConfiguration('household_name', $_householdname);
    $eqLogic->setConfiguration('type', 'device');
    if(isset($_device['category'])){
      $eqLogic->setConfiguration('category', $_device['category']);
    }
    if(isset($_device['parent_device_id'])){
      $eqLogic->setConfiguration('parent_device_id', $_device['parent_device_id']);
    }
    if(isset($_device['product_id'])){
      $eqLogic->setConfiguration('product_id', $_device['product_id']);
    }
    if(isset($_device['serial_number'])){
      $eqLogic->setConfiguration('serial_number', $_device['serial_number']);
    }
    $eqLogic->setConfiguration('surepetcare_id', $_device['surepetcare_id']);
    $products = $eqLogic->getConfiguration('products',array());
    if (!in_array($_device['product_id'],$products)){
      $products[]=$_device['product_id'];
    }
    if ($eqLogic->getConfiguration('iconProduct','') == ''){
      $eqLogic->setConfiguration('iconProduct','device'. $_device['product_id'].'.png');
    }
    $eqLogic->setConfiguration('products', $products);
    $eqLogic->save();
    if(file_exists(__DIR__.'/../config/products/device'.$_device['product_id'].'.json')){
      log::add('surepetcare','debug','Found config file for product id ' . $_device['product_id']);
      $products = json_decode(file_get_contents(__DIR__.'/../config/products/device'.$_device['product_id'].'.json'),true);
      log::add('surepetcare','debug','Products : '.file_get_contents(__DIR__.'/../config/products/device'.$_device['product_id'].'.json'));
      $eqLogic->setConfiguration('product_name', $products['configuration']['product_name']);
      $eqLogic->save();
      $link_cmds = array();
      foreach ($products['commands'] as $product) {
         log::add('surepetcare','debug','Commande : '.json_encode($product));
        $cmd = $eqLogic->getCmd(null,$deviceIdList[1].'.'.$product['logicalId']);
        if(is_object($cmd)){
          continue;
        }
        $cmd = new surepetcareCmd();
        utils::a2o($cmd,$product);
        $cmd->setLogicalId($deviceIdList[1].'.'.$product['logicalId']);
        $cmd->setEqLogic_id($eqLogic->getId());
        $cmd->save();
        if (isset($product['value'])) {
          $link_cmds[$cmd->getId()] = $product['value'];
        }
      }
    }
    if (count($link_cmds) > 0) {
      foreach ($eqLogic->getCmd() as $eqLogic_cmd) {
        foreach ($link_cmds as $cmd_id => $link_cmd) {
          if ($link_cmd == $eqLogic_cmd->getName()) {
            $cmd = cmd::byId($cmd_id);
            if (is_object($cmd)) {
              $cmd->setValue($eqLogic_cmd->getId());
              $cmd->save();
            }
          }
        }
      }
    }
    $updatedValue = $eqLogic->applyData($_device);
    if($create){
      event::add('surepetcare::includeDevice', $eqLogic->getId());
    }
    return $eqLogic;
  }

  public static function findPet($_pet,$_householdid, $_householdname) {
    $create = false;
    $eqLogic = self::byLogicalId($_pet['id'], 'surepetcare');
    if(!is_object($eqLogic)){
       log::add('surepetcare','debug','new pet '.$_pet['id']);
      event::add('jeedom::alert', array(
        'level' => 'warning',
        'page' => 'surepetcare',
        'message' => __('Nouvel animal detecté', __FILE__),
      ));
      $create = true;
      $eqLogic = new surepetcare();
      $eqLogic->setName($_pet['name']);
    }
    $eqLogic->setEqType_name('surepetcare');
    $eqLogic->setIsEnable(1);
    $eqLogic->setLogicalId($_pet['id']);
    $eqLogic->setConfiguration('household_id', $_householdid);
    $eqLogic->setConfiguration('household_name', $_householdname);
    $eqLogic->setConfiguration('type', 'pet');
    if(isset($_pet['category'])){
      $eqLogic->setConfiguration('category', $_pet['category']);
    }
    if(isset($_pet['gender'])){
      $eqLogic->setConfiguration('gender', $_pet['gender']);
    }
    if(isset($_pet['weight'])){
      $eqLogic->setConfiguration('weight', $_pet['weight']);
    }
    if(isset($_pet['photo']['location'])){
      $eqLogic->setConfiguration('photo_location', $_pet['photo']['location']);
    }
    if(isset($_pet['comments'])){
      $eqLogic->setConfiguration('comments', $_pet['comments']);
    }
    if(isset($_pet['breed_id'])){
      $eqLogic->setConfiguration('breed_id', $_pet['breed_id']);
      $eqLogic->setConfiguration('breed_name', surepetcare::breedname($_pet['breed_id']));
    }
    if(isset($_pet['food_type_id'])){
      $eqLogic->setConfiguration('food_type_id', $_pet['food_type_id']);
    }
    if(isset($_pet['species_id'])){
      $eqLogic->setConfiguration('species_id', $_pet['species_id']);
    }
    if(isset($_pet['tag_id'])){
      $eqLogic->setConfiguration('tag_id', $_pet['tag_id']);
    }
    $eqLogic->save();

    return $eqLogic;
  }
  public static function devicesParameters($_device = '') {
    log::add('surepetcare', 'debug', 'debut de devicesParameters');
    $return = array();
    foreach (ls(dirname(__FILE__) . '/../config/devices', '*') as $dir) {
      $path = dirname(__FILE__) . '/../config/devices/' . $dir;
      if (!is_dir($path)) {
        continue;
      }
      log::add('surepetcare', 'debug', 'devicesParameters path '.$path);
      $files = ls($path, '*.json', false, array('files', 'quiet'));
      foreach ($files as $file) {
        try {
          $content = file_get_contents($path . '/' . $file);
          if (is_json($content)) {
            $return += json_decode($content, true);
          }
        } catch (Exception $e) {
        }
      }
    }
    if (isset($_device) && $_device != '') {
      if (isset($return[$_device])) {
        return $return[$_device];
      }
      return array();
    }
    log::add('surepetcare', 'debug', 'devicesParameters return '.json_encode($return));
    return $return;
  }
    /*     * *********************Méthodes d'instance************************* */
  public function postSave() {
    log::add('surepetcare', 'debug', 'debut de postSave');
    if ($this->getConfiguration('applyProductId') != $this->getConfiguration('product_id')) {
      log::add('surepetcare', 'debug', 'postSave envoi vers applyModuleConfiguration');
      $this->applyModuleConfiguration();
    }
  }

  public function getImage() {
    If ($this->getConfiguration('type') == 'device') {
      return 'plugins/surepetcare/core/config/images/' . $this->getConfiguration('iconProduct');
    } else if ($this->getConfiguration('type') == 'pet') {
      return $this->getConfiguration('photo_location');
    } else {
      return 'plugins/surepetcare/plugin_info/surepetcare_icon.png';
    }
  }

  public function applyModuleConfiguration() {
    log::add('surepetcare', 'debug', 'debut de applyModuleConfiguration');
    log::add('surepetcare', 'debug', 'product_id='.$this->getConfiguration('product_id'));
    $this->setConfiguration('applyProductId', $this->getConfiguration('product_id'));
    $this->save();
    if ($this->getConfiguration('product_id') == '') {
      log::add('surepetcare', 'debug', 'applyModuleConfiguration retour true');
      return true;
    }
    log::add('surepetcare', 'debug', 'applyModuleConfiguration envoi vers devicesParameters');
    $device = self::devicesParameters($this->getConfiguration('product_id'));
    if (!is_array($device)) {
      return true;
    }
    log::add('surepetcare', 'debug', 'applyModuleConfiguration import' . print_r($device));
    $this->import($device);
  }

    public function preInsert() {

    }

    public function postInsert() {

    }

    public function preSave() {

    }

    public function preUpdate() {

    }

    public function postUpdate() {

    }

    public function preRemove() {

    }

    public function postRemove() {

    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class surepetcareCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
        if ($this->getType() != 'action') {
            return;
        }
        $eqLogic = $this->getEqLogic();
        $type = $eqLogic->getConfiguration('type', '');
        $actionerId = $eqLogic->getLogicalId();
        $logicalId = $this->getLogicalId();
        $actionDatas = explode('.',$logicalId);
        if ($type == 'device') {
            $url = 'https://app.api.surehub.io/api/device/' . $actionerId . '/control';
        }
        if ($type =='pet') {
            $url = 'https://app.api.surehub.io/api/pet/' . $actionerId . '/position';
        }
        log::add('surepetcare', 'debug', 'execute url='.$url);
    }

    /*     * **********************Getteur Setteur*************************** */
}
