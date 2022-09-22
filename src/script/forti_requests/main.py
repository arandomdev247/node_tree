#! /usr/bin/env python

# Fichier principal du programme de gestion de la base de données de fortiADC
# Version 1.0
# Auteur : Benjamin ADAMCZYK
# Date : 01/07/2022
# Mail : benjamin.adamczyk@outlook.fr


import argparse
from sys import exit
from os import path

import data_worker_enhanced
from config_reader import ConfigReader


def args_manager():
    """
    Gère les arguments passés en ligne de commande
    """

    parser_creator = argparse.ArgumentParser(description="Gestion de la base de données de fortiADC")
    parser_creator.add_argument("-v", "--version", action="version", version="%(prog)s 1.0")
    parser_creator.add_argument("-j", "--json", help="Force la création d'un fichier JSON et outrepasse le fichier INI",
                                action="store_true")
    parser_creator.add_argument("-d", "--dot", help="Force la création d'un fichier DOT et outrepasse le fichier INI.",
                                action="store_true")
    parser_creator.add_argument("-i", "--image", help="Force la création d'une image et outrepasse le fichier INI.",
                                action="store_true")
    parser_creator.add_argument("-a", "--affiche", help="Affiche les données", action="store_true")
    parser_creator.add_argument("-s", "--silence", help="N'affiche pas les messages. Utilisé pour les scripts.",
                                action="store_true")
    parser_creator.add_argument("-m", "--machine", help="Outrepasse les préférences et génère uniquement"
                                                        "un fichier JSON en silencieux (utilisé comme API)",
                                action="store_true")

    return parser_creator


def main(args_main=None):
    current_dir = path.dirname(__file__)

    # Read config file
    config = ConfigReader(path.join(current_dir) + "/data/config.ini")

    ip = config.ip()
    user = config.user()
    pwd = config.password()
    is_err = False

    if ip == 'err' or user is None or pwd is None:
        return -2

    arg_silence = args_main.silence if args_main is not None else False
    arg_json = args_main.json if args_main is not None else False
    arg_dot = args_main.dot if args_main is not None else False
    arg_image = args_main.image if args_main is not None else False
    arg_affiche = args_main.affiche if args_main is not None else False
    arg_machine = args_main.machine if args_main is not None else False

    if arg_machine:
        arg_silence = True
        arg_json = True
        arg_dot = False
        arg_image = False
        arg_affiche = False

    data = data_worker_enhanced.worker(ip, user, pwd, arg_silence)
    if data is None:
        if not arg_silence:
            print("Aucune donnee trouvee")
        return -1
    else:
        if not arg_silence:
            print("Donnees recuperees")
        if config.show_data() or arg_affiche:
            print('-----------------------------------------------------')
            print(data)
            print('-----------------------------------------------------')

    if config.json_generate() or arg_json:
        if data_worker_enhanced.export_dictionnary(data, path.join(current_dir, config.json_path())):
            if not arg_silence:
                print("Fichier JSON genere avec succes")
        else:
            if not arg_silence:
                print("Erreur lors de la generation du fichier JSON")
            is_err = True

    if config.dot_generate() or arg_dot:
        if data_worker_enhanced.export_dot(data, path.join(current_dir, config.dot_path())):
            if not arg_silence:
                print("Fichier DOT genere avec succes")
        else:
            if not arg_silence:
                print("Erreur lors de la generation du fichier DOT")
            is_err = True

    if config.image_generate() or arg_image:
        if data_worker_enhanced.export_img(data, path.join(current_dir, config.image_path())):
            if not arg_silence:
                print("Image generee avec succes")
        else:
            if not arg_silence:
                print("Erreur lors de la generation du fichier image")
            is_err = True

    if is_err:
        return -1
    else:
        return 2


if __name__ == '__main__':

    parser = args_manager()
    args = parser.parse_args()

    final_return = main(args)

    exit(final_return)
