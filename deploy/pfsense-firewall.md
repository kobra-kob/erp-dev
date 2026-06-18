# Pare-feu pfSense — ArtisanFlow en DMZ

Le serveur ArtisanFlow (Debian LAMP) est placé en **DMZ** : exposé depuis Internet
uniquement sur HTTP/HTTPS, isolé du réseau LAN interne.

## Topologie

```
        Internet (WAN)
             │
        ┌────┴─────┐
        │ pfSense  │
        └─┬──────┬─┘
   LAN ───┘      └─── DMZ
 (postes)        (serveur ArtisanFlow  ex. 10.0.10.10)
```

## 1. Interface DMZ
- **Interfaces → Assignments** : assigner une interface dédiée à la DMZ
  (ex. réseau `10.0.10.0/24`, IP pfSense `10.0.10.1`).
- Donner au serveur une IP fixe (ex. `10.0.10.10`).

## 2. NAT — Port forwarding (WAN → serveur)
**Firewall → NAT → Port Forward** :

| Interface | Proto | Port dest. | Redirige vers | Port |
|-----------|-------|-----------|---------------|------|
| WAN       | TCP   | 443 (HTTPS) | 10.0.10.10  | 443  |
| WAN       | TCP   | 80 (HTTP)   | 10.0.10.10  | 80   |

> Le port 80 sert uniquement à la redirection vers HTTPS et au renouvellement
> Let's Encrypt (challenge ACME). pfSense crée automatiquement la règle WAN associée.

## 3. Règles de pare-feu (principe : tout bloquer sauf le nécessaire)

**Règles WAN** (créées par le NAT) :
- ✅ Autoriser `WAN → 10.0.10.10` sur TCP 80 et 443.
- ⛔ Tout le reste vers la DMZ : bloqué (deny par défaut).

**Règles DMZ** (onglet Firewall → Rules → DMZ) :
- ⛔ **Bloquer `DMZ → LAN`** (le serveur ne doit jamais initier de connexion vers le réseau interne) — règle de tête, destination = LAN net, action *Block*.
- ✅ Autoriser `DMZ → WAN` TCP 80/443 (mises à jour apt, ACME).
- ✅ Autoriser `DMZ → WAN` TCP 587/465 (envoi SMTP des relances de factures).
- ✅ Autoriser `DMZ → tout` UDP 53 (DNS) — ou pointer vers un résolveur dédié.
- ✅ Autoriser `DMZ → serveur Zabbix` TCP 10051 (envoi des données) si le serveur Zabbix est sur le LAN/un autre segment.
- ⛔ Bloquer le reste.

## 4. Supervision
- Si le serveur Zabbix est hors DMZ, ajouter une règle ciblée autorisant
  `Zabbix-server → 10.0.10.10:10050` (collecte agent) et
  `10.0.10.10 → Zabbix-server:10051` (mode actif).

## 5. Durcissement complémentaire
- Activer le **log** sur les règles de blocage DMZ→LAN pour tracer les tentatives.
- Restreindre l'accès SSH d'administration au serveur depuis le LAN/VPN uniquement
  (jamais depuis le WAN) — règle DMZ ou via le pare-feu local `ufw`.
