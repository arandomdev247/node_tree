# Fichier principal permettant la gestion des données

import json
from collections import OrderedDict

import anytree.node.node
from anytree import Node, RenderTree
from anytree.dotexport import RenderTreeGraph
from anytree.exporter import JsonExporter, DictExporter
import operator
from functools import reduce
from datetime import datetime

import forti_requests


# --- Fonctions de récupération et filtrage des données ---


def get_data_forti_request(fr_dom: forti_requests.Request, silence=False):
    """
    Fait les requêtes à FortiADC et renvoie les données
    :return: Si la requête a réussi, renvoie les données, sinon renvoie None
    """

    data_host = fr_dom.get_global_load_balance_host()
    data_vsp = fr_dom.get_global_load_balance_virtual_server_pool()
    data_vs = fr_dom.get_load_balance_virtual_server()
    data_rsp = fr_dom.get_load_balance_pool()
    data_rs = fr_dom.get_load_balance_real_server()

    # Check les erreurs
    if data_host.status_code != 200:
        if not silence:
            err_infos("FQDN - Host", data_host.status_code)
        return None
    elif data_vsp.status_code != 200:
        if not silence:
            err_infos("FQDN - VSP (Virtual Server Pool)", data_vsp.status_code)
        return None
    elif data_vs.status_code != 200:
        if not silence:
            err_infos("VS (Virtual Server)", data_vs.status_code)
        return None
    elif data_rsp.status_code != 200:
        if not silence:
            err_infos("RSP (Real Server Pool)", data_rsp.status_code)
        return None
    elif data_rs.status_code != 200:
        if not silence:
            err_infos("RS (Real Server)", data_rs.status_code)

    if not silence:
        print("Toutes les requetes ont ete effectuees avec succes !")

    data_host = data_host.json()
    data_host = data_host["payload"]

    data_vsp = data_vsp.json()
    data_vsp = data_vsp["payload"]

    data_vs = data_vs.json()
    data_vs = data_vs["payload"]

    data_rsp = data_rsp.json()
    data_rsp = data_rsp["payload"]

    data_rs = data_rs.json()
    data_rs = data_rs["payload"]

    return data_host, data_vsp, data_vs, data_rsp, data_rs


def data_filter_host(data_host):
    """
    Filtre les données pour ne garder que les données utiles dans les Hosts

    :param list data_host: Liste des données de la requête : get_global_load_balance_host()
    :return: Liste filtrée de données
    """

    final_list = []

    for items in data_host:
        name = fullname_translator(str(items["host-name"]), items["domain-name"])
        final_list.append([name, items["persistence"], items["respond-single-record"],
                           items["load-balance-method"], items["vs_pool_list_count"]])

    for i, items in enumerate(data_host):

        count = final_list[i][4]

        if count == 1:
            final_list[i][4] = [get_by_path(items, ["vs_pool_list", 0, "virtual-server-pool"])]
        else:
            final_list[i][4] = []
            for y in range(0, count):
                final_list[i][4].append(get_by_path(items, ["vs_pool_list", y, "virtual-server-pool"]))

    return final_list


def data_filter_vsp(data_vsp):
    """
    Filtre les données pour ne garder que les données utiles dans les VSP

    :param list data_vsp: Liste des données de la requête : get_global_load_balance_virtual_server_pool()
    :return: Liste filtrée de données
    """

    final_list = []

    for items in data_vsp:
        final_list.append([items["mkey"], items["check-server-status"],
                           items["check-virtual-server-existent"], items["vs_pool_member_count"]])

    for i, items in enumerate(data_vsp):

        count = final_list[i][3]

        if count == 1:
            final_list[i][3] = [get_by_path(items, ["vs_pool_member", 0, "server-member-name"]),
                                get_by_path(items, ["vs_pool_member", 0, "server"])]
        else:
            final_list[i][3] = []
            for y in range(0, count):
                final_list[i][3].append([get_by_path(items, ["vs_pool_member", y, "server-member-name"]),
                                         get_by_path(items, ["vs_pool_member", y, "server"])])

    return final_list


def data_filter_vs(data_vs):
    """
    Filtre les données pour ne garder que les données utiles dans les VS

    :param list data_vs: Liste des données de la requête :get_load_balance_virtual_server()
    :return: Liste filtrée de données
    """

    final_list = []

    for items in data_vs:
        final_list.append([items["mkey"], items["addr-type"], items["address"], items["address6"],
                           items["availability"], items["interface"], items["method"], items["packet-fwd-method"],
                           items["port"], items["pool"], items["profile"], items["status"], items["type"]])

    return final_list


def data_filter_rsp(data_rsp):
    """
    Filtre les données pour ne garder que les données utiles dans les RSP

    :param data_rsp: Liste des données de la requête :get_load_balance_pool()
    :return: Liste filtrée de données
    """

    final_list = []

    for items in data_rsp:
        final_list.append([items["mkey"], items["availability"], items["pool_member_count"]])

    for i, items in enumerate(data_rsp):

        count = final_list[i][2]

        if count == 1:
            final_list[i][2] = [get_by_path(items, ["pool_member", 0, "real_server_id"])]
        else:
            final_list[i][2] = []
            for y in range(0, count):
                final_list[i][2].append([get_by_path(items, ["pool_member", y, "real_server_id"])])

    return final_list


def data_merger(fr_dom: forti_requests.Request, silence=False):
    """
    Récupère les données et compile le tout dans un arbre à visualiser ou exporter
    :param fr_dom: fortiRequests.Request
    :param silence: booléen pour ne pas afficher les messages d'erreur
    :return: Tree structure si la requête fonctionne, None sinon
    """

    #  [0] = data_host, [1] = data_vsp, [2] = data_vs, [3] = data_rsp, [4] = data_rs
    my_tuple = get_data_forti_request(fr_dom, silence)

    if my_tuple is None:
        return None

    dict_host = my_tuple[0]
    dict_vsp = my_tuple[1]
    dict_vs = my_tuple[2]
    dict_rsp = my_tuple[3]
    dict_rs = my_tuple[4]

    root_host = Node("root_host")
    root_vsp = Node("root_vsp")
    root_vs = Node("root_vs")
    root_rsp = Node("root_rsp")
    root_rs = Node("root_rs")

    # Création des nodes pour les hosts
    for item_host in dict_host:

        item_host['fullname'] = fullname_translator(item_host.get('host-name'), item_host.get('domain-name'))
        new_node = Node(item_host.get('fullname'))
        new_node.parent = root_host

        for key, value in item_host.items():
            if key != 'vs_pool_list' and not is_private_key(key):
                setattr(new_node, key, value)
            elif key == 'vs_pool_list' and not is_private_key(key):
                for hostname in item_host.get('vs_pool_list'):
                    vsp_node = Node(hostname.get('virtual-server-pool'))
                    vsp_node.parent = new_node

    # Création des nodes pour les VSP
    for items_vsp in dict_vsp:

        vsp_node = Node(items_vsp.get('mkey'))
        vsp_node.parent = root_vsp

        for key, value in items_vsp.items():
            if key != 'vs_pool_member' and not is_private_key(key):
                setattr(vsp_node, key, value)
            elif key == 'vs_pool_member' and not is_private_key(key):
                for hostname in items_vsp.get('vs_pool_member'):
                    vs_node = Node(hostname.get('server-member-name'))
                    vs_node.parent = vsp_node

    # Création des nodes pour les VS
    for items_vs in dict_vs:

        vs_node = Node(items_vs.get('mkey'))
        vs_node.parent = root_vs

        for key, value in items_vs.items():
            if not is_private_key(key):
                setattr(vs_node, key, value)

    # Création des nodes pour les RSP

    for items_rsp in dict_rsp:

        rsp_node = Node(items_rsp.get('mkey'))
        rsp_node.parent = root_rsp

        for key, value in items_rsp.items():

            if key != 'pool_member' and not is_private_key(key):
                setattr(rsp_node, key, value)
                pass
            elif key == 'pool_member' and not is_private_key(key):
                for hostname in items_rsp.get('pool_member'):
                    final_node = Node(hostname.get('real_server_id'))
                    final_node.parent = rsp_node

    # Création des nodes pour les RS

    for items_rs in dict_rs:
        rs_node = Node(items_rs.get('mkey'))
        rs_node.parent = root_rs

        for key, value in items_rs.items():
            if not is_private_key(key):
                setattr(rs_node, key, value)

    # Fusion des branches

    merge_tree_rsp_rs(root_rsp, root_rs)
    merge_tree_vs_rsp(root_vs, root_rsp)
    merge_tree_vsp_vs(root_vsp, root_vs)
    merge_tree_host_vsp(root_host, root_vsp)

    return root_host


# --- Fonctions utilitaire ---

def err_infos(text, code_error):
    """
    Affiche les informations en cas d'erreur de requête
    :param str text: String contenant la requête concernée
    :param int code_error: Code d'erreur de la requête
    :return: None
    """
    print(f"Erreur lors de la recuperation de donnees dans {text} !\nCode : {code_error}")


def fullname_translator(host_name, domain_name):
    """
    Récupère le nom de domaine et le nom d'hôte pour en faire un nom de domaine complet
    :param string host_name: nom de l'hôte (ex: 'accord-client')
    :param str domain_name: nom de domaine (ex: 'es.fr_test')
    :return: str: nom de domaine complet (ex: 'accord-client.es.fr_test')
    """

    name = str(host_name) + "." + str(domain_name)
    if name[-1] == ".":
        return name[:-1]  # On enlève le dernier caractère qui est un "."
    else:
        return name


def safe_search(list_, element):
    """
    Retourne la position d'un élément dans une liste. Si l'élément n'est pas dans la liste, retourne -1.
    :param list list_: Liste dans laquelle on recherche l'élément
    :param element: Element à rechercher
    :return: int: Index de l'élément dans la liste, -1 si l'élément n'est pas dans la liste
    """

    try:
        return list_.index(element)
    except ValueError:
        return -1


def safe_search_all(list_, element):
    """
    Retourne une table comportant toutes les occurrences de l'élément dans la liste.
    Si l'élément n'est pas dans la liste, retourne une liste vide.
    :param list_: List dans laquelle on recherche l'élément
    :param element: Element à rechercher
    :return: list: Liste contenant les indices de l'élément dans la liste, vide si l'élément n'est pas dans la liste
    """
    try:
        return [i for i, x in enumerate(list_) if x == element]
    except ValueError:
        return []


def get_by_path(root, items):
    """Accède à un objet imbriqué dans root par une séquence d'items.
    :param root: Objet racine
    :param items: Liste d'items
    :return: Objet ou None
    """
    return reduce(operator.getitem, items, root)


def export_dot(node_to_export, path, filename="fortiADC.dot", silence=False):
    """
    Exporte l'arbre en fichier dot (Il FAUT que GraphViz soit installé sur le système)
    :param node_to_export: Arbre à exporter
    :param path: Nom du fichier à exporter
    :param filename: Nom du fichier à exporter
    :param silence: Si True, ne pas afficher les messages d'erreur
    :return: None
    """

    if path.endswith("/") or path.endswith("\\"):
        path = path[:-1]
    filename = path + '/' + generate_datetime_on_file(filename)

    try:
        RenderTreeGraph(node_to_export).to_dotfile(filename)
        return True
    except Exception as e:
        if not silence:
            print(f"Erreur lors de l export du graphe : {e}")
            print("Est-ce que Graphviz est bien installe ?")
        return False


def export_img(node_to_export, path, filename="fortiADC_data.png"):
    """
    Exporte l'arbre en fichier image
    Il FAUT que GraphViz soit installé sur le système et que l'extension du fichier soit supportée
    :param node_to_export: Arbre à exporter
    :param path: Répertoire d'export
    :param filename: Nom du fichier à exporter
    :return: None
    """

    if path.endswith("/") or path.endswith("\\"):
        path = path[:-1]
    filename = path + '/' + generate_datetime_on_file(filename)

    try:
        RenderTreeGraph(node_to_export).to_picture(filename)
        return True
    except Exception as e:
        print(f"Erreur lors de l export en image : {e}")
        print("Est-ce que Graphviz est bien installe ?")
        return False


def export_json(node_to_export, path, filename="fortiADC_data.json", silence=False):
    """
    Exporte un node en format JSON
    :param node_to_export: Arbre à exporter
    :param path: Répertoire d'export
    :param filename: Nom du fichier à exporter, relatif au répertoire courant
    :param silence: Si True, ne pas afficher les messages d'erreur
    :return: None
    """

    if path.endswith("/") or path.endswith("\\"):
        path = path[:-1]
    filename = path + '/' + generate_datetime_on_file(filename)

    try:
        with open(filename, 'w') as f:
            exporter = JsonExporter(indent=4, sort_keys=True)
            exporter.write(node_to_export, f)
        return True
    except IOError as e:
        if not silence:
            print("Erreur lors de l ecriture du fichier JSON : " + str(e))
        return False


def export_dictionnary(node_to_export, path, filename="fortiADC_data.json"):
    """
    Exporte un dictionnaire en JSON
    :param node_to_export: Arbre à exporter
    :param path: Répertoire d'export
    :param filename: Nom du fichier à exporter, relatif au répertoire courant
    :return: None
    """

    if path.endswith("/") or path.endswith("\\"):
        path = path[:-1]
    filename = path + '/' + generate_datetime_on_file(filename)

    exporter = DictExporter(dictcls=OrderedDict, attriter=sorted)
    dict_to_export = exporter.export(node_to_export)

    json_str = json.dumps(dict_to_export, indent=4)

    try:
        with open(filename, 'w') as f:
            f.write(json_str)
        return True
    except IOError as e:
        print("Erreur lors de l ecriture du fichier JSON : " + str(e))
        return False


def check_extension(filename, extension):
    """
    Vérifie l'extension d'un fichier
    :param filename: Nom du fichier
    :param extension: Extension à vérifier
    :return: True si l'extension est correcte, False sinon
    """
    return filename.endswith(extension)


def generate_datetime_on_file(filename):
    """
    Génère un nom de fichier pour un fichier de type datetime
    :param filename: Nom du fichier à générer
    :return: str: Nom du fichier avec la date et l'heure
    """
    return datetime.now().strftime("%Y_%m_%d-%H_%M_%S") + "_" + filename


def error_handler(error):
    """
    Gestionnaire d'erreur
    :param int error: Erreur
    :return: None
    """
    if error == 404:
        print("Erreur de connexion au serveur FortiADC : "
              "Vérifiez l adresse IP et le status du serveur.")
        print("Code d'erreur : ", error)
    elif error == 401:
        print("Erreur de connexion au serveur FortiADC : "
              "Verifiez l'adresse IP et le statut du serveur.")
        print("Code d'erreur : ", error)
    else:
        print("Erreur de connexion au serveur FortiADC : "
              "Erreur inconnue.")
        print("Code d'erreur : ", error)


def is_private_key(key):
    """
    Vérifie si la clé du dictionnaire est privée
    :param key: Clé du dictionnaire
    :return: bool: True si la clé est privée, False sinon
    """
    return key.startswith('_')


def create_node_copy(node_to_copy):
    """
    Créé une copie du node passé en paramètre
    :param node_to_copy: Node à copier
    :return: node: Node copié
    """

    tmp_node = Node(node_to_copy.name)

    for key, value in node_to_copy.__dict__.items():
        if is_private_key(key):
            continue
        else:
            setattr(tmp_node, key, value)

    if node_to_copy.children:
        for child in node_to_copy.children:
            tmp_child_node = create_node_copy(child)
            tmp_child_node.parent = tmp_node

    return tmp_node


def merge_tree_rsp_rs(root_rsp, root_rs):
    """
    Fusionne les arbres RSP et RS
    Utilisation de la clé primaire mkey du RS pour trouver le lien avec le RSP correspondant et en fait le parent
    :param root_rsp: Arbre RSP
    :param root_rs: Arbre RS
    :return: Arbre RSP fusionné
    """

    for real_server_pool in root_rsp.children:
        print(real_server_pool)
        for rs_member in real_server_pool.children:
            print(rs_member)
            for rs_items in root_rs.children:
                if rs_items.mkey == rs_member.name:
                    tmp_node = create_node_copy(rs_items)
                    tmp_node.parent = rs_member

    return root_rsp


def merge_tree_vs_rsp(root_vs, root_rsp):
    """
    Fusionne les arbres VS et RSP
    Utilisation de la clé primaire mkey du RSP pour trouver le lien avec le VS correspondant et en fait le parent
    :param root_vs: Arbre VS
    :param root_rsp: Arbre RSP
    :return: Arbre fusionné
    """

    for virtual_server in root_vs.children:
        for pool in root_rsp.children:
            if pool.name == virtual_server.pool:
                tmp_node = create_node_copy(pool)
                tmp_node.parent = virtual_server
    return root_vs


def merge_tree_vsp_vs(root_vsp, root_vs):
    """
    Fusionne les arbres VSP et VS
    Utilisation de la clé primaire mkey du VS pour trouver le lien avec le VSP correspondant et en fait le parent
    :param root_vsp: Arbre VSP
    :param root_vs: Arbre VS
    :return: Arbre fusionné
    """

    for virtual_server_pool in root_vsp.children:
        for vs_member in virtual_server_pool.children:
            for vs_items in root_vs.children:
                if vs_items.name == vs_member.name:
                    tmp_node = create_node_copy(vs_items)
                    tmp_node.parent = vs_member

    return root_vsp


def merge_tree_host_vsp(root_host, root_vsp):
    """
    Fusionne les arbres Host et VSP
    Utilisation de la clé primaire mkey du VSP pour trouver le lien avec le Host correspondant et en fait le parent
    :param root_host: Arbre Host
    :param root_vsp: Arbre VSP
    :return: Arbre fusionné
    """

    for hostnames in root_host.children:
        if hostnames.vs_pool_list_count == 1:
            for vsp_items in root_vsp.children:
                if vsp_items.name == hostnames.children[0].name:
                    tmp_node = create_node_copy(vsp_items)
                    tmp_node.parent = hostnames.children[0]
        else:
            for vs_pool_list_items in hostnames.children:
                for vsp_items in root_vsp.children:
                    if vsp_items.name == vs_pool_list_items.name:
                        tmp_node = create_node_copy(vsp_items)
                        tmp_node.parent = vs_pool_list_items

    return root_host


def worker(ip_forti, user_forti, password_forti, silence=False):
    """
    Fonction principale de traitement et d'exécution de la commande
    :param str ip_forti: Adresse IP du serveur FortiADC
    :param user_forti: nom d'utilisateur pour se connecter au serveur
    :param password_forti: mot de passe pour se connecter au serveur
    :param bool silence: Si True, ne pas afficher les messages de sortie
    :return: Boolean: 0 si tout s'est bien passé, -1 sinon
    """

    fr = forti_requests.Request(ip_forti, user_forti, password_forti)
    fr_login_data = fr.login()

    if isinstance(fr_login_data, int) and fr_login_data == 404:
        if not silence:
            error_handler(404)
        return -1
    elif fr_login_data.status_code == 401:
        if not silence:
            error_handler(401)
        return -1
    elif fr_login_data.status_code != 200:
        if not silence:
            error_handler(-1)
        return -1

    final_data = data_merger(fr, silence)
    fr.logout()

    return final_data


if __name__ == '__main__':
    # Create a new instance of the fortiRequests class

    ip = "https://__.__.__.__"
    user = ""
    pwd = ""
    name_of_the_domain = "host_v2.1_final.json"
    name_of_the_image = "host_v2.1_final.png"

    fr_test = forti_requests.Request(ip, user, pwd)
    fr_test.login()
    data = data_merger(fr_test)

    if isinstance(data, anytree.node.node.Node):
        print(RenderTree(data))
        print("Done")
        export_img(data, name_of_the_image)
    else:
        print("Error")
        print(data)

    fr_test.logout()
