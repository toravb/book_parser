from sys import argv
import os
import urllib.request
from bs4 import BeautifulSoup
import json
import socket
import time
import random
import re


def returnError(code, uri=None, mess=None):
    page = {
        'Url': uri,
        'Code': code,
        'Message': mess,
    }
    jsonData = json.dumps(page)

    print(jsonData)
    exit()


def req(uri):
    # Пауза парсера
    # time.sleep(random.randrange(91, 1127) / 1000)
    try:
        req = urllib.request.Request(uri, headers={'User-Agent': 'Mozilla/5.0'})
        webpage = urllib.request.urlopen(req)
    except urllib.error.HTTPError as e:
        mess = str(e)
        returnError(e.code, uri, mess)
        return False
    except socket.error:
        returnError(0, uri, 'proxy')
    except Exception as detail:
        returnError(0, uri, type(detail).__name__)
    else:
        return webpage


def parseLinks(uri, domain):
    link = uri
    i = 1
    links = []
    while i <= 29:
        soup = req(link+str(i))
        page = BeautifulSoup(soup.read(), 'lxml')
        list = page.find_all('a', class_='letter_nav_s')
        for a in list:
            links.append({'link': domain + '/' + a.get('href')})
        i += 1
    jsonData = json.dumps(links)
    print(jsonData)


def parseBook(uri, domain):
    link = uri
    soup = req(link)
    page = BeautifulSoup(soup.read(), 'lxml')
    td_top_color = page.find('tr', class_='td_top_color')
    td_center_color = page.find_all('tr', class_='td_center_color')
    params = td_center_color[0].find('p').text
    params = re.sub(r'\t+', '', params)
    params = re.sub(r'\r+', '', params)

    params = params.split('\n')
    book_params = []
    i = 0
    while i < len(params):
        if params[i]:
            j = 0
            param = params[i].split(': ')
            while j < len(param):
                if param[j]:
                    book_params.append(param[j])
                j += 1
        i += 1

    i = 0
    book = {}
    book['book'] = {}
    book['book']['search'] = {}
    book['book']['params'] = {}
    params = {}
    params['params'] = {}
    while i < len(book_params):
        if book_params[i] == 'Серия':
            book['book']['search']['series'] = book_params[i+1]
        elif book_params[i] == 'Название':
            params['title'] = book_params[i + 1]
        elif book_params[i] == 'Автор':
            sub_str = ''
            while book_params[i+1] != 'Название':
                sub_str += book_params[i+1]
                i += 1
            i += 1
            book['book']['search']['author'] = sub_str
            continue
        elif book_params[i] == 'Издательство':
            book['book']['search']['publisher'] = book_params[i + 1]
        elif book_params[i] == 'Год':
            book['book']['search']['year'] = book_params[i + 1]
        else:
            params['params'][book_params[i]] = book_params[i+1]
        i += 2
    params['params']['Жанр'] = td_top_color.find('p').text.split('Жанр ')[1]
    book['book']['params'] = params
    book['book']['params']['text'] = re.sub(r'\s+', ' ', td_center_color[1].find('p', class_='span_str').text)
    book['pages'] = parsePage(domain+'/read_book.php?'+uri.split('?')[1]+'&p=1', 'link', domain)
    book['image'] = {'link': domain+'/'+td_center_color[0].find('img').get('src')}

    jsonData = json.dumps(book)
    print(jsonData)



def parsePage(uri, type, domain):
    try:
        soup = req(uri)
        page = BeautifulSoup(soup.read(), 'lxml')
        page.find_all('div', attrs={'style': 'text-align: left; font-size: 0.8em; margin-bottom: 10px;'})[0].decompose()
        page.find_all('div', attrs={'style': 'text-align: right; font-size: 0.8em; margin-top: 10px;'})[0].decompose()
        content = page.find('div', class_='MsoNormal')

        if type == 'link':
            nav = page.find('div', class_='navigation').find_all('a')
            pages = nav[len(nav) - 2].text
            url = uri[:-1]
            urls = []
            i = 1
            while i <= int(pages):
                urls.append({'link': url+str(i)})
                i+=1
            return urls
        else:
            page_content = {}
            page_content['content'] = content.prettify()
            page_content['img'] = []
            imgs = content.find_all('img')
            i = 0
            while i < len(imgs):
                page_content['img'].append(domain+'/'+imgs[i].get('src'))
                i += 1
            # print(content.encode('utf-8'))

            print(page_content)

        # f = open('1.html', 'w', encoding='utf-8')
        # f.write(str(content))
        # f.close()
        # i = 2
        # while i <= int(pages):

            # soup = req(url+str(i))
            # page = BeautifulSoup(soup.read(), 'lxml')
            # page.find_all('div', attrs={'style': 'text-align: left; font-size: 0.8em; margin-bottom: 10px;'})[
            #     0].decompose()
            # page.find_all('div', attrs={'style': 'text-align: right; font-size: 0.8em; margin-top: 10px;'})[
            #     0].decompose()
            # content = page.find('div', class_='MsoNormal').prettify()
            # print(content)
            # f = open(str(i)+'.html', 'w', encoding='utf-8')
            # f.write(str(content))
            # f.close()
            # i+=1

    except Exception as detail:
        returnError(0, uri, type(detail).__name__)


def parseImage(uri):
    image = req(uri).read()
    path = uri.split('/')
    img_dir = path[3] + '/' + path[4] + '/'
    if not os.path.isdir(img_dir):
        os.makedirs(img_dir)
    f = open(img_dir + path[5], "wb")
    f.write(image)
    f.close()
    print(True)


def parse(argv):
    # Точка входа
    try:
        script, url, proxy, type = argv
    except ValueError:
        returnError(code=0, mess='Parameter is null')

    proxy_host = proxy
    uri = url

    domain_str = list(filter(None, url.split('/')))
    domain = domain_str[0] + '//' + domain_str[1]
    # # Подключение прокси
    # socket.setdefaulttimeout(60)
    # proxy_support = urllib.request.ProxyHandler({
    #     'http': proxy_host,
    #     'https': proxy_host,
    # })
    # opener = urllib.request.build_opener(proxy_support)
    # urllib.request.install_opener(opener)

    if type == 'links':
        parseLinks(uri, domain)
    elif type == 'book':
        parseBook(uri, domain)
    elif type == 'page':
        parsePage(uri, type, domain)
    elif type == 'image':
        parseImage(uri)

    # parsePage(uri, proxy)
    # parseList()
    # jsonData = json.dumps(result)
    # print(jsonData)
    exit()


parse(argv)