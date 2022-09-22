import configparser


class ConfigReader:

    def __init__(self, path_to_init='data/config.ini'):
        self.config = configparser.ConfigParser()
        self.config.read(path_to_init)

    def conf(self):
        return self.config

    def ip(self):
        return self.config.get('login', 'server-ip', fallback='err')

    def user(self):
        return self.config.get('login', 'user', fallback=None)

    def password(self):
        return self.config.get('login', 'password', fallback=None)

    def json_path(self):
        return self.config.get('export-json', 'default-path-json', fallback='/')

    def json_generate(self):
        return self.config.getboolean('export-json', 'generate-json', fallback=True)

    def dot_path(self):
        return self.config.get('export-dot', 'default-path-dot', fallback='/')

    def dot_generate(self):
        return self.config.getboolean('export-dot', 'generate-dot', fallback=True)

    def image_path(self):
        return self.config.get('export-image', 'default-path-image', fallback='/')

    def image_generate(self):
        return self.config.getboolean('export-image', 'generate-image', fallback=True)

    def show_data(self):
        return self.config.getboolean('other', 'show-data', fallback=True)

# TODO :
# Faire le système de protection en cas de fichier introuvable