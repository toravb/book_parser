from sys import argv
import os
import urllib.request
from bs4 import BeautifulSoup
import json
import socket
import time
import random
import re
import config

class Parser(object):

    def __init__(self):
        self.conf = None
        self.parse(argv)


    def returnError(self, code, uri=None, mess=None):
        page = {
            'Url': uri,
            'Code': code,
            'Message': mess,
        }
        jsonData = json.dumps(page)

        print(jsonData)
        exit()

    def req(self, uri):
        # Пауза парсера
        time.sleep(random.randrange(91, 1127) / 1000)
        try:
            req = urllib.request.Request(uri, headers={'User-Agent': 'Mozilla/5.0'})
            webpage = urllib.request.urlopen(req)
        except urllib.error.HTTPError as e:
            mess = str(e)
            if str(e.code)[0] == '5':
                self.returnError(e.code, uri, mess)
            elif e.code == 403:
                self.returnError(e.code, uri, 'proxy')
            elif str(e.code)[0] == '4':
                self.returnError(e.code, uri, mess)
            elif str(e.code)[0] == '3':
                self.returnError(e.code, uri, mess)
            return False
        except socket.error:
            self.returnError(0, uri, 'proxy')
        else:
            return webpage

    def parseSiteMap(self, uri):
        # Парсинг sitemap
        # Парсинг xml
        urls = []
        soup = BeautifulSoup(self.req(uri).read(), 'xml')
        for url in soup.find_all('loc'):
            urls.append(url.text)
        return urls

    def parseTab(self, soup, search, domain):
        try:
            if search == 'series':
                search = 'Товары этой же серии'
            elif search == 'accessories':
                search = 'Аксессуары'
            elif search == 'components':
                search = 'Комплектующие'
            else:
                return 0
            # Заполнение комплектующих
            Components = []
            # Components = ''
            div = soup.find('div',
                            class_=self.conf['tab']['div'])
            tab_index = 0

            for tab in div.find_all('span', class_=self.conf['tab']['tab']):
                if tab.text == search:
                    break
                tab_index += 1
            div = soup.find('div', attrs={self.conf['tab']['tab-index']: tab_index})
            if div == None:
                return [0]
            for a in div.find_all('a', class_=self.conf['tab']['link']):
                domain_a = list(filter(None, a['href'].split('/')))
                link = domain + a['href']
                if 0 <= 1 < len(domain_a):
                    full_domain = domain_a[0] + '//' + domain_a[1]
                    if full_domain == domain:
                        link = a['href']
                # link = list(filter(None, a['href'].split('/')))

                Components.append(link)
                # if link[0] == 'product' or link[0] == 'products':
                #     Components.append(link[1])
                # Components += link[1] + ','
                # else:
                #     Components.append(link[0])
                # Components += link[0] + ','
            return Components
        except Exception as detail:
            return type(detail).__name__

    def parseName(self, soup):
        try:
            Articul = self.parseArticul(soup)
            Name = soup.h1.text.strip(' \t\n\r').replace('\xa0', '').replace('\n', '').split(" " + Articul)[0]
            return Name
        except Exception as detail:
            return type(detail).__name__

    def parseArticul(self, soup):
        try:
            ul = soup.find('ul', class_=self.conf['Articul']['list'])
            div = ul.find_all('div')
            for i in range(len(div)):
                if div[i].text.strip(' \t\n\r').replace('\xa0', '').replace('\n', '') == 'Артикул:' or div[i].text.strip(' \t\n\r').replace('\xa0', '').replace('\n', '') == 'Код:':
                    Articul = div[i + 1].text.strip(' \t\n\r').replace('\xa0', '').replace('\n', '')
                    break
            return Articul
        except Exception as detail:
            return type(detail).__name__

    def parseParams(self, soup):
        try:
            Params = {}
            # Заполнение параметров
            params_group = soup.find_all('div', self.conf['Params']['params_group'])
            del params_group[0]
            for element in params_group:
                for div in element.find_all('div', self.conf['Params']['div_delete']):
                    div.decompose()
                for div in element.find_all('div', self.conf['Params']['div']):
                    value = div.find('div', self.conf['Params']['div_value'])
                    if value == None:
                        value = div.find('div', self.conf['Params']['div_value_desc'])
                    name_text = value.text.strip(' \t\n\r').replace('\xa0', '').replace('\n', '')
                    if div.find('div', 'hint hint--dark-x-blue hint--position-y-top detail-spec__hint-toggle') != None:
                        value_text = div.find('div',
                                              'hint hint--dark-x-blue hint--position-y-top detail-spec__hint-toggle').text.strip(
                            ' \t\n\r').replace('\xa0', '').replace('\n', '')
                    else:
                        value_text = div.find('div',
                                              'product-summary__spec-value product-summary__spec-value--x-size').text.strip(
                            ' \t\n\r').replace('\xa0', '').replace('\n', '')
                    value_text = re.sub(r'\s+', ' ', value_text)
                    Params[name_text] = value_text
            return Params
        except Exception as detail:
            return type(detail).__name__

    def parseIsAvailable(self, soup):
        try:
            Is_available = 1
            if soup.find('div', class_=self.conf['IsAvailable']['div']) != None:
                Is_available = 0
            return Is_available
        except Exception as detail:
            return type(detail).__name__

    def parsePrice(self, soup):
        try:
            if self.parseIsAvailable(soup):
                if soup.find('div', class_=self.conf['Price']['action']) != None:
                    Price = soup.find('div', class_=self.conf['Price']['action']).text.strip(' \t\n\r').replace(
                        '\xa0', '').replace('\n', '')
                else:
                    Price = soup.find('div', class_=self.conf['Price']['current']).text.strip(' \t\n\r').replace('\xa0', '').replace('\n',
                                                                                                                       '')
                return Price.split(' ')[0]
            return 0
        except Exception as detail:
            return type(detail).__name__

    def parsePriceAction(self, soup):
        try:
            if self.parseIsAvailable(soup):
                if soup.find('div', class_=self.conf['Price']['action']) != None:
                    Price = soup.find('div', class_=self.conf['Price']['current']).text.strip(' \t\n\r').replace('\xa0', '').replace('\n',
                                                                                                                       '')
                    return Price.split(' ')[0]
            return 0
        except Exception as detail:
            return type(detail).__name__

    def parseQuant(self, soup):
        try:
            if self.parseIsAvailable(soup):
                Quantity = 0
                div = soup.find('div', class_=self.conf['Quantity']['div'])

                if div.find('div', class_=self.conf['Quantity']['available']):
                    Quantity = div.find('div', class_=self.conf['Quantity']['available']).text.strip(' \t\n\r').replace(
                        '\xa0', '').replace('\n', '')
                    Quantity = re.sub(r'\s+', ' ', Quantity)
                    num = Quantity.split(' ')[2]
                    try:
                        int(num)
                        Quantity = num
                    except ValueError:
                        pass
                elif div.find('div', class_=self.conf['Quantity']['l_available']):
                    Quantity = soup.find('div', class_=self.conf['Quantity']['l_available']).text.strip(' \t\n\r').replace(
                        '\xa0', '').replace('\n', '')
                elif div.find('div', class_=self.conf['Quantity']['u_available']):
                    Quantity = div.find('div', class_=self.conf['Quantity']['u_available']).text.strip(
                        ' \t\n\r').replace('\xa0', '').replace('\n', '')
                    Quantity = re.sub(r'\s+', ' ', Quantity)
                return Quantity
            return 0
        except Exception as detail:
            return type(detail).__name__

    def parseImage(self, soup, uri, parse_type, itemId, siteId):
        if parse_type == 'link':
            div = soup.find('div', class_=self.conf['Image']['div'])
            images = div.find_all('span', {'data-zoom-href': True})
            links = []
            for image in images:
                links.append(image['data-zoom-href'])
            return links

        path = str(itemId.zfill(8))
        full_path = 'img/' + siteId + '/' + path[0] + path[1] + '/' + path[2] + path[3] + '/' + path[4] + path[5] + '/' + \
                    path[6] + path[7] + '/'

        # Создание дирректории
        if not os.path.isdir(full_path):
            os.makedirs(full_path)

        # status = []
        # div = soup.find('div', class_='col col--xs-auto product-summary__col-1 product-summary__gallery')
        # img = div.find('span', {'data-zoom-href': True})
        # if parse_type == 'main' or parse_type == 'full':
        index = 1
        while True:
            if os.path.exists(full_path + str(index) + ".jpg"):
                index += 1
            else:
                break
        try:
            f = open(full_path + str(index) + ".jpg", "wb")
            f.write(soup)
            f.close()
        except Exception as detail:
            self.returnError(0, uri, type(detail).__name__)
        else:
            page = {
                'Status': 'Success',
                'Url': uri,
            }
            return page
        # if parse_type != 'main':
        #     div = soup.find('div', class_='col col--xs-auto product-summary__col-1 product-summary__gallery')
        #     imgs = div.find_all('span', {'data-zoom-href': True})
        #     index = 2
        #     for image in imgs:
        #         if image['data-zoom-href'] == img['data-zoom-href']:
        #             continue
        #         try:
        #             f = open('img/'+i+'/'+i+"-"+str(index)+".jpg", "wb")
        #             f.write(req(image['data-zoom-href']).read())
        #             f.close()
        #         except Exception as detail:
        #             status.append(type(detail).__name__)
        #         else:
        #             status.append(1)
        #         index += 1

    def parsePage(self, soup, uri, itemId, domain, siteId):
        page = {
            'Name': self.parseName(soup),
            'Articul': self.parseArticul(soup),
            'Url': uri,
            'Is_available': self.parseIsAvailable(soup),
            'Price': self.parsePrice(soup),
            'Price_action': self.parsePriceAction(soup),
            'Quantity': self.parseQuant(soup),
            'Series': self.parseTab(soup, 'series', domain),
            'Components': self.parseTab(soup, 'components', domain),
            'Accessories': self.parseTab(soup, 'accessories', domain),
            'Params': self.parseParams(soup),
            'Images': self.parseImage(soup, uri, 'link', itemId, siteId),
        }

        return page

    def parse(self, argv):
        # Точка входа
        try:
            script, url, proxy, parse_type, itemId, siteId = argv
        except ValueError:
            self.returnError(code=0, mess='Parameter is null')

        proxy_host = proxy
        uri = url
        domain_str = list(filter(None, url.split('/')))
        domain = domain_str[0] + '//' + domain_str[1]

        if domain_str[1] == 'basicdecor.ru':
            self.conf = config.Config.siteData(self)['basicdecor']

        # Подключение прокси
        # socket.setdefaulttimeout(60)
        # proxy_support = urllib.request.ProxyHandler({
        #     'http': proxy_host,
        #     'https': proxy_host,
        # })
        # opener = urllib.request.build_opener(proxy_support)
        # urllib.request.install_opener(opener)

        result = None
        soup = False
        if parse_type == 'sitemap':
            result = self.parseSiteMap(uri)
        elif parse_type == 'image':
            soup = self.req(uri).read()
            if BeautifulSoup(soup, 'lxml').find('div', class_=self.conf['403']) != None:
                self.returnError(0, uri, 'proxy')
            result = self.parseImage(soup, uri, 'full', itemId, siteId)
        else:
            soup = BeautifulSoup(self.req(uri).read(), 'lxml')
            if soup.find('div', class_=self.conf['403']) != None:
                self.returnError(0, uri, 'proxy')
            result = self.parsePage(soup, uri, itemId, domain, siteId)

        jsonData = json.dumps(result)
        print(jsonData)
        exit()


Parser()
