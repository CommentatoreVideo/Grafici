from django.shortcuts import render
import urllib,json
import re
# Create your views here.

def trovaData(s,p):
  lunghezza=range(0,len(p))
  for i in lunghezza:
    if(s==p[i]):
      return i
  return len(p)

def dividiPeriodi(intestazione):
  periodi=intestazione.split("&")
  regex=re.compile("\d\d_\d\d_\d\d\d\d")
  periodi = [ i for i in periodi if regex.match(i) ]
  return periodi
  

def index(request):
  #Preso il file
  url="https://www.umanetexpo.net/expo2015Server/UECDL/grafici/as_1920/report&4Ct$6.asp"
  json_url=urllib.request.urlopen(url)
  data=json.loads(json_url.read())
  #Divido i periodi
  periodi=dividiPeriodi(data["intestazione"])
  numeroPeriodi=len(periodi)
  periodoInizio=-1
  periodoFine=-1
  periodoFineM=0
  
  
  if(request.GET.get("periodoInizio",-1)!=-1):
    if(request.GET.get("periodoInizio")=="12_09_2013"):
      periodoInizio:0
    else:
      periodoInizioM=request.GET.get("periodoInizio")
      regex=re.compile("\d\d_\d\d_\d\d\d\d")
      if(regex.match(periodoInizioM)):
        periodoInizio=trovaData(periodoInizioM,periodi)
      else:
        periodoInizio:0
  else:
    periodoInizio:0
    
  if(request.GET.get("periodoFine",-1)!=-1):
    periodoFineM=request.GET.get("periodoFine")
    if(periodoFineM=="oggi"):
      periodoFine:numeroPeriodi-1
    else:
      regex=re.compile("\d\d_\d\d_\d\d\d\d")
      if(regex.match(periodoFineM)):
        periodoFineM=trovaData(periodoFineM,periodi)
      if(periodoFineM<periodoInizio):
        periodoFine=periodoInizio-1
      else:
        periodoFine=periodoFineM
  else:
    periodoFine=numeroPeriodi
  #carico la pagina
  context={"periodi":periodi,"periodoInizio":periodoInizio,"periodoFine":periodoFine,"periodoFineM":periodoFineM}
  return render(request,"indice.html",context)