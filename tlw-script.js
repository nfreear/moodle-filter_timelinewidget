/* Timeline Widget Loader script. */
var TLW = TLW || {};
TLW.include=function(filetype, opts){ //(url,filetype,callback,inner)
  var ref;
  if (filetype==="js"){ //JavaScript.
    ref=document.createElement("script");
    //ref.type="text/javascript";
    if(typeof opts.url!=="undefined"){
      ref.src=opts.url; //setAttribute("src", url);
    }
    if(typeof opts.callback!=="undefined"){
      ref.onreadystatechange=callback;
      ref.onload=callback;
    }
    //ref.id =typeof opts.id!="undefined" ? opts.id : ''; 
    //MSIE bug?
  } else if (filetype==="css"){ //External CSS file.
    ref=document.createElement("link");
    ref.rel="stylesheet";
    ref.type="text/css";
    ref.href=opts.url;
  }
  if (opts.inner!=="undefined"){
    //document.write('iH '+ref.innerHTML+'; iT '+ref.innerText+'; tC '+ref.textContent);//Debug.

    if(ref.innerHTML!=="undefined"){ ref.innerHTML = opts.inner;}//Firefox.
    else if(ref.innerText!=="undefined")  {ref.innerText = opts.inner;}
    else if(ref.textContent!=="undefined"){ref.textContent=opts.inner;}
    else{ ref.appendChild(document.createTextNode(opts.inner));}//Safari.
    //ref.text = opts.inner;
    //YAHOO.util.Element.setContent(ref, opts.inner);
  }
  if (typeof ref!=="undefined"){
    //var where = opts.where!=="undefined" ? opts.where : "head";
    document.getElementsByTagName("head")[0].appendChild(ref);
  }
};
