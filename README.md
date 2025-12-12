## zero knowledge diary
* Laravel 10 (PHP 8.2)
* SQLite

## about
* 일전의 next.js로 만들었던 일기장을 php로 다시 만듦
* 중간에 생각 잘못해서 postgresql에서 sqlite로 바꿨는데 아직 컨테이너는 뜨고있어서 걷어내야됨(todo)
* 제로 널리지 다이어리란 무엇인가.. 서버에 평문이 저장되지 않는 일기장을 말한다.
    - 공개 일기 / 비공개 일기로 나뉘는데 공개 일기는 viram.diary.dev/@{username}에서 모두 공개된다. 
    - 공개 일기는 평문으로 저장된다.
    - 비공개 일기는 계정 패스워드를 (패스워드 테이블에 들어가는 암호화된 패스워드와는 다른 알고리즘으로) 해쉬화한 diaryKey로 클라이언트에서 암호화되어 저장되며 이 키는 서버로 전송되지 않는다.
    - 그 말인즉슨 패스워드를 변경하면 모든 비공개 일기가 복호화/재암호화 되어 저장되어야 하는 구조이다. (아직구현안함ㅋ)
    - 일기와 관련된 모든 작업을 할 때 패스워드를 입력하여 diaryKey를 계산할 수는 없으므로 localStorage에 diaryKey를 저장하기 때문에 XSS공격에 취약해서 여기에 신경을 많이 써야한다. 
    - 이게 보안적으로 괜찮은 구조인지는 잘몰르겟지만,, 어쨌건 내 일기가 서버에 평문저장된다는 건 서버 주인이 직업윤리를 지킬거라는 믿음을 가져야 하는 일이기 때문에 그러지 '못하는' 구조를 만들어보고 싶엇음

```
docker-compose up -d --build
docker-compose exec app composer install --working-dir=/var/www
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
```