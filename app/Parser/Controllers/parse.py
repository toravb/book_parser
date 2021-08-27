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


def parseList(uri):
    link = uri
    i = 1
    links = []
    while i <= 29:
        soup = req(link+str(i))
        page = BeautifulSoup(soup.read(), 'lxml')
        list = page.find_all('a', class_='letter_nav_s')
        for a in list:
            links.append(a.get('href'))
        i += 1
    print(links)


def parseBook(uri, domain):
    link = uri
    soup = req(link)
    page = BeautifulSoup(soup.read(), 'lxml')
    td_top_color = page.find('tr', class_='td_top_color')
    td_center_color = page.find_all('tr', class_='td_center_color')
    params = td_center_color[0].find('p').text
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
    params = {}
    while i < len(book_params):
        params[book_params[i]] = book_params[i+1]
        i+=2
    params['Жанр'] = td_top_color.find('p').text.split('Жанр ')[1]
    book = {}
    book['params'] = params
    book['text'] = re.sub(r'\s+', ' ', td_center_color[1].find('p', class_='span_str').text)
    book['pages'] = parsePage(domain+'/read_book.php?'+uri.split('?')[1]+'&p=1', 'link')
    book['preview_image'] = domain+'/'+td_center_color[0].find('img').get('src')
    print(book)
    # print(params.split('\n'))


def parsePage(uri, type):
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
                urls.append(url+str(i))
                i+=1
            return urls
        else:
            content.find_all('img')
            # print(content.encode('utf-8'))

            print(content.prettify())

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


def parse(argv):
    # Точка входа
    try:
        script, url, proxy, type, itemId, siteId = argv
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

    if type == 'list':
        parseList(uri)
    elif type == 'book':
        parseBook(uri, domain)
    elif type == 'page':
        parsePage(uri,proxy)

    # parsePage(uri, proxy)
    # parseList()
    # jsonData = json.dumps(result)
    # print(jsonData)
    exit()


parse(argv)
