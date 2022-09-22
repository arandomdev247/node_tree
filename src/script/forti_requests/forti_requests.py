import json
import pprint

import requests
import time

# Utilisé uniquement pour désactiver les warnings SSL
# FortiADC n'ayant pas de certificat SSL
import urllib3.exceptions
from urllib3 import disable_warnings

# Désactive les warnings SSL
disable_warnings(urllib3.exceptions.InsecureRequestWarning)


class Request:

    def __init__(self, req_ip, req_user="", req_password="", token="", data=None):
        """
        Classe contenant les requêtes pour la connexion à FortiADC

        :param str req_ip : Adresse IP de l'ADC
        :param str req_user : Utilisateur
        :param str req_password : Mot de passe
        :param token : Token de connexion
        :param data : Données renvoyées par la requête
        """

        # Variables de connexion
        self.ip = req_ip
        self.user = req_user
        self.pwd = req_password
        self.token = token
        self.data = data

        # Variables utilisée pour le header de la requête
        self.content = {"Content-Type": "applications/json"}
        self.accept = {"Accept": "application/json"}

        # Cookie avec le temps epoch pour la requête. Important pour la connexion. (Ne pas supprimer)
        self.cookie = {"Cookie": "last_access_time=" + str(round(time.time()))}

        # Header primordial pour la requête. Contient le token de connexion
        self.authorization = {"Authorization": "Bearer " + str(self.token)}

    def update_authorization(self):
        """
        Met à jour le header pour les autorisations
        Important ! Ne pas supprimer, 'authorization' ne se met pas à jour automatiquement sinon
        :return: None
        """

        self.authorization = {"Authorization": "Bearer " + str(self.token)}

    def login(self):
        """
        Requête pour la connexion à FortiADC
        :return : code de réponse de la requête
        """

        url = self.ip + "/api/user/login"
        my_header = self.content | self.accept
        my_auth = {"username": self.user,
                   "password": self.pwd}

        try:
            r = requests.post(url, headers=my_header, json=my_auth, verify=False)
        except requests.exceptions.ConnectTimeout:
            return 404

        if r.status_code == 200:
            tmp_data = json.loads(r.text)
            self.data = tmp_data
            self.token = tmp_data["token"]
            self.update_authorization()

        return r

    def logout(self):
        """
        Requête pour la déconnexion à FortiADC
        :return : code de réponse de la requête
        """

        url = self.ip + "/api/user/logout"
        my_header = self.authorization

        return requests.get(url, headers=my_header, verify=False)

    def get_all_vs_info(self):
        """

        :return:
        """

        my_header = self.authorization | self.cookie | self.accept

        r = self.request_maker("/api/all_vs_info/vs_list", my_header)
        return r

    def get_global_load_balance_host(self):
        """
        Renvoie la liste des hosts dans le load balance global (utilisé pour le FQDN - Host)
        :return: Liste de JSON
        """

        my_header = self.authorization | self.cookie | self.accept

        r = self.request_maker("/api/global_load_balance_host", my_header)
        return r

    def get_global_load_balance_virtual_server_pool(self):
        """
        Renvoie la liste des pools dans le load balance global (utilisé pour le FQDN - VSP)
        :return: Liste de JSON
        """

        my_header = self.authorization | self.cookie | self.accept

        r = self.request_maker("/api/global_load_balance_virtual_server_pool", my_header)
        return r

    def get_load_balance_virtual_server(self):
        """
        Renvoie la liste des VS dans le load balance (utilisé pour le VS)
        :return: Liste de JSON
        """

        my_header = self.authorization | self.cookie | self.accept

        r = self.request_maker("/api/load_balance_virtual_server", my_header)
        return r

    def get_load_balance_pool(self):
        """
        Renvoie la liste des pools dans le load balance (utilisé pour RSP)
        :return: Liste de JSON
        """

        my_header = self.authorization | self.cookie | self.accept

        r = self.request_maker("/api/load_balance_pool", my_header)
        return r

    def get_load_balance_real_server(self):
        """
        Renvoie la liste des Nodes présents dans Real Server (utilisé pour RS)
        :return: Liste de JSON
        """

        my_header = self.authorization | self.cookie | self.accept

        r = self.request_maker("/api/load_balance_real_server", my_header)
        return r

    # Main function to make requests
    def request_maker(self, api_url, header, payload=None):

        url = self.ip + api_url

        if payload is None:
            r = requests.get(url, headers=header, verify=False)
        else:
            r = requests.post(url, headers=header, data=payload, verify=False)

        return r


if __name__ == "__main__":
    print("Test de connexion à FortiADC")

    ip = "https://__.__.__.__"

    user = ""
    pwd = ""

    req = Request(ip, user, pwd)
    req.login()
    # print(req.get_all_vs_status_last_2_slb().text)
    # my_data = req.get_load_balance_virtual_server()
    my_data = req.get_load_balance_real_server()
    # print(my_data.text)

    if my_data.status_code == 200:
        my_dict = json.loads(my_data.text)
        my_dict = my_dict["payload"]
        pprint.pprint(my_dict, indent=4, width=80)
    else:
        print("Erreur de connexion")

    req.logout()

    print("Fin du test")
