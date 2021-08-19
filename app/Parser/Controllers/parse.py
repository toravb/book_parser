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


def parsePage(uri, proxy):
    try:
        soup = req(uri)
        page = BeautifulSoup(soup.read(), 'lxml')
        page.find_all('div', attrs={'style': 'text-align: left; font-size: 0.8em; margin-bottom: 10px;'})[0].decompose()
        page.find_all('div', attrs={'style': 'text-align: right; font-size: 0.8em; margin-top: 10px;'})[0].decompose()
        content = page.find('div', class_='MsoNormal').prettify()

        if proxy == '0':
            nav = page.find('div', class_='navigation').find_all('a')
            pages = nav[len(nav) - 2].text
            url = uri[:-1]
            urls = ''
            i = 1
            while i <= int(pages):
                urls += url+str(i)+','
                i+=1
            print(urls)
        else:
            print(content.encode('utf-8'))
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
        script, url, proxy, itemId, siteId = argv
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

    parsePage(uri, proxy)
    # jsonData = json.dumps(result)
    # print(jsonData)
    exit()


parse(argv)
