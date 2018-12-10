prompt PL/SQL Developer Export Tables for user U_2900Z@REGION19
prompt Created by D09-Turkevych on 10 ЦПСДЕМЭ 2018 П.
set feedback off
set define off

prompt Dropping D_STAN...
drop table D_STAN cascade constraints;
prompt Creating D_STAN...
create table D_STAN
(
  c_stan  NUMBER(2) not null,
  n_stan  VARCHAR2(60) not null,
  d_begin DATE not null,
  d_dict  DATE not null,
  d_end   DATE not null,
  sort    NUMBER(2)
)
;
grant select on D_STAN to IVASYKB;
grant select on D_STAN to PIKALKA;

prompt Disabling triggers for D_STAN...
alter table D_STAN disable all triggers;
prompt Loading D_STAN...
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (0, 'окюрмхй ондюрйIб гю нямнбмхл лIяжел накIйс', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 0);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (1, 'окюрмхй ме ондюб гюъбх дкъ бгърръ мю накIй', to_date('17-11-2017', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 46);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (3, 'опхимърн пIь-мъ опн опхо-мъ (пнгонвюрн кIйб-имс опнжедспс)', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 1);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (4, 'онпсьемн опнбюдфеммъ с яопюбI опн аюмйпсрярбн', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 45);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (5, 'онпсьемн опнбюдфеммъ с яопюбI опн опхохмеммъ (кIйбIдюжIч)', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 49);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (6, 'бйкчвемхи дн ╙дп г бIдлIрйнч, ын ябIднжрбн медIияме', to_date('01-09-2012', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 55);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (7, 'дн ╙дп бмеяемн гюохя опн бIдясрмIярэ оIдрбепдфеммъ бIднлняре', to_date('16-06-2007', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 67);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (8, 'дн ╙дп бмеяемн гюохя опн бIдясрмIярэ гю лIяжегмюундфеммъл', to_date('16-06-2007', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 69);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (9, 'мюопюбкемн онбIднлкеммъ гю т. ╧ 18-ноо дн 07.09.2015', to_date('07-11-2006', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('21-10-2015', 'dd-mm-yyyy'), 81);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (10, 'гюохр мю бярюмнбкеммъ лIяжегмюундфеммъ (лIяжъ опнфхбюммъ)', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2011', 'dd-mm-yyyy'), 84);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (11, 'опхохмемн, юке ме гмърн г накIйс (йнп ме осярI)', to_date('16-06-2007', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 4);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (12, 'опхохмемн, юке ме гмърн г накIйс (йнп осярI, юке ме гюйпхрI)', to_date('16-06-2007', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 3);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (13, 'рпхбю╙ опнжедспю яюмюжI╞ анпфмхйю', to_date('01-11-2006', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 57);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (14, 'бхгмюмн аюмйпсрнл', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 59);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (15, 'оепеиьнб дн Iмьн╞ доI', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 78);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (16, 'опхохмемн (кIйбIднбюмн, гюйпхрн)', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 95);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (17, 'окюрмхй ондюрйIб гю менямнбмхл лIяжел накIйс', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 79);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (18, 'рпхбю╙ опнж-пю опхо-мъ (мюдюмю днб-йю опн бIдя-Iярэ гюанпцнб', to_date('30-09-2006', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 2);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (19, 'рпхбю╙ опнж-пю опхо-мъ (днб-йю опн бIдя-ярэ гюанпц.ме дIиямю', to_date('30-09-2006', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('01-03-2016', 'dd-mm-yyyy'), 80);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (20, 'онремжIимхи окюрмхй (бIднлнярI г ╙дхмнцн депфюбмнцн пе╙ярпс)', to_date('16-08-2004', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('02-11-2005', 'dd-mm-yyyy'), 82);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (21, 'кIйбIдюжIимю опнжедспю гю пIьеммъл (онярюмнбнч) ясдс', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 58);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (22, 'гмърн г накIйс гю нямнбмхл лIяжел накIйс', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 68);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (23, 'лIяжегмюундфеммъ ме бярюмнбкемн', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2011', 'dd-mm-yyyy'), 87);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (24, 'оепб.опнтяоIкйю,ын ме лю╙ гбIкэмемху вкемIб бханп.опнтнпцюм.', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2011', 'dd-mm-yyyy'), 86);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (25, 'слнбмхи окюрмхй', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 98);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (26, 'ачдфермю нпцюмIгюжIъ(нпц.депф.бкюдх рю Iм),ын тIмюмя.г ачдф.', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2011', 'dd-mm-yyyy'), 83);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (27, 'гмърн г накIйс гю менямнбмхл лIяжел накIйс', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 97);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (28, 'рпхбю╙ опнж-пю опхо-мъ (мюопюбкемI гюоепевеммъ нпцюмс дтя)', to_date('19-12-2011', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 56);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (30, 'б ярюмI оепеундс дн Iмьн╞ доI', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 5);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (31, 'рпхбю╙ яопныемю опнжедспю опхохмеммъ', to_date('21-06-2011', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 44);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (32, 'тюйрхвмю юдпеяю ме бярюмнбкемю, чп.юдпеяю люянбн╞ пе╙ярпюжI╞', to_date('17-12-2009', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2011', 'dd-mm-yyyy'), 85);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (36, 'нямнбмхи окюрмхй дн йIмжъ пнйс', to_date('02-04-2004', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('16-06-2014', 'dd-mm-yyyy'), 88);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (37, 'ме ╙ окюрмхйнл ондюрйIб', to_date('01-01-1990', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 99);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (45, 'окюрмхй дн йIмжъ пнйс (менямнбме лIяже накIйс)', to_date('06-06-2014', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 47);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (50, 'б ярюмI оепеундс г Iмьн╞ доI', to_date('17-12-2009', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 48);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (56, 'окюрмхй г мюярсомнцн пнйс', to_date('26-03-2004', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('16-06-2014', 'dd-mm-yyyy'), 89);
insert into D_STAN (c_stan, n_stan, d_begin, d_dict, d_end, sort)
values (65, 'окюрмхй г мюярсомнцн пнйс (нямнбме лIяже накIйс)', to_date('06-06-2014', 'dd-mm-yyyy'), to_date('22-11-2017', 'dd-mm-yyyy'), to_date('31-12-2999', 'dd-mm-yyyy'), 96);
commit;
prompt 37 records loaded
prompt Enabling triggers for D_STAN...
alter table D_STAN enable all triggers;

set feedback on
set define on
prompt Done
