# dotProject BR

**dotProject BR** é a continuação brasileira do [dotProject](https://www.dotproject.net/), o gerenciador de projetos livre em PHP e MySQL criado em 2000. Mantido pela comunidade em [dotproject.com.br](https://dotproject.com.br/).

O que este fork faz:

- **Roda em PHP 8.3** (MariaDB/MySQL), mantendo compatibilidade com bancos do dotProject 2.1.x e 2.2.
- **Português do Brasil** como idioma de primeira classe, a partir da tradução da antiga Comunidade dotProject Brasil (veja `locales/pt_br/CREDITOS.md`).
- **Atualização de banco por linha de comando**: `php install/upgrade_cli.php` (use `--dry-run` para simular).
- Correções de compatibilidade com o PHP 8 (gráficos de Gantt, departamentos, tickets, tradução e outras), sempre marcadas no código com `dotProject BR:`.
- Próximo passo: interface repaginada.

## Rodando localmente

```bash
docker compose up -d --build     # PHP 8.3 + MariaDB 11.8 em http://localhost:8080
```

Abra `http://localhost:8080/install/` para uma instalação nova. Para testar com um banco existente, aponte `DP_DADOS` para uma pasta **fora do repositório** com os dumps `.sql` (veja `docker-compose.yml`). Nunca coloque dados reais dentro do repositório.

## Atualizando uma instalação existente

1. Faça backup do banco e da pasta `files/`.
2. Publique o código novo mantendo `includes/config.php` (troque `dbtype` de `mysql` para `mysqli`) e a pasta `files/`.
3. Rode `php install/upgrade_cli.php` com o PHP 8.3.

## Licença e créditos

GNU GPL v2, como o dotProject original (veja `COPYING` e `LICENSE.md`). Todo o histórico e a autoria do projeto original estão preservados neste repositório. dotProject BR é um projeto independente da comunidade; o projeto original está em [github.com/dotproject/dotProject](https://github.com/dotproject/dotProject).

---

*Below is the original dotProject README.*

![dotProject Logo](style/nord/images/favicons/android-chrome-96x96.png)
# dotProject
**dotProject** is an open source project management system written in PHP.

It originally started in 2001 by dotMarketing on SourceForge and has
been under the watchful eye of the current dotProject team since around December 2002.

## Installing/Upgrading

**NOTE** The `devel` branch is where all the development happens.  If you want the latest and greatest with all relevent bug fixes between releases, then download from https://github.com/dotproject/dotProject/archive/devel.zip

`master` tracks the current release.

**The Quick Way**: Point your browser to the `install` directory.

## Support

Bug reports and other issues can be lodged on GitHub at https://github.com/dotproject/dotProject/issues

## License

As of version 2.0, dotProject is released under [GPL](LICENSE.md).
1.0.2 and previous versions were released under BSD license.
Parts of dotProject include libraries from other projects which are used and re-released under their original licence.

## Docker composer support

The latest devel branch now includes a simple `docker-compose.yml` file and support files.  These will allow you to run dotProject by running:

`docker-compose up`

This will set up an nginx container, a phpfpm container and a mariadb container and point the web server to the base directory of dotProject.  All you need to do after that is point your browser to http://localhost/

`docker-compose down` will retain any data you have created.  If you want to start again from a clean slate, use `docker-compose down -v` to remove the database volume.

