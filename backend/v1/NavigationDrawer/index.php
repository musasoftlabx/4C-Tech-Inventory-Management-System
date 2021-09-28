<?php
require '../functions.php';

/*$_JWT = file_get_contents('php://input');
Tokenify($_JWT, false);
$DealerCode = TokenDeconstruct($_JWT, 'DealerCode');*/

$PARENTS = $DB->prepare("SELECT *
    FROM NavigationDrawerParents
    WHERE visible = true
    ORDER BY priority");
$PARENTS->execute();

$parents = [];

while ($PARENT_ROWS = $PARENTS->fetch()) {
    $parent_pk       = $PARENT_ROWS['PK'];
    $SELECT_CHILDREN = $DB->prepare("SELECT *
        FROM NavigationDrawerChildren
        WHERE ParentFK = $parent_pk
        AND visible = true
        ORDER BY priority");
    $SELECT_CHILDREN->execute();

    $children = [];

    while ($CHILDREN_ROWS = $SELECT_CHILDREN->fetch()) {
        $child_pk             = $CHILDREN_ROWS['PK'];
        $SELECT_GRANDCHILDREN = $DB->query("SELECT text, route, icon
            FROM NavigationDrawerGrandChildren
            WHERE ChildFK = $child_pk
            AND visible = true
            ORDER BY text")->fetchAll();
        if ($CHILDREN_ROWS['grandchildren'] == 'false') {
            array_push($children,
                [
                    'text'  => $CHILDREN_ROWS['text'],
                    'icon'  => $CHILDREN_ROWS['icon'],
                    'route' => $CHILDREN_ROWS['route'],
                ]
            );
        } else {
            array_push($children,
                [
                    'text'          => $CHILDREN_ROWS['text'],
                    'icon'          => $CHILDREN_ROWS['icon'],
                    'grandchildren' => $SELECT_GRANDCHILDREN,
                ]
            );
        }
    }

    if ($PARENT_ROWS['children'] == 'false') {
        array_push($parents,
            [
                'text'     => $PARENT_ROWS['text'],
                'icon'     => $PARENT_ROWS['icon'],
                'route'    => $PARENT_ROWS['route'],
                'expanded' => $PARENT_ROWS['expanded'] == 'true' ? true : false,
                'new'      => date_diff(date_create(date('Y-m-d')), date_create(explode(' ', $PARENT_ROWS['modified'])[0]))->format("%a") <= 30 ? true : false,
            ]
        );
    } else {
        array_push($parents,
            [
                'text'     => $PARENT_ROWS['text'],
                'icon'     => $PARENT_ROWS['icon'],
                'icon-alt' => $PARENT_ROWS['icon_alt'],
                'expanded' => $PARENT_ROWS['expanded'] == 'true' ? true : false,
                'children' => $children,
            ]
        );
    }
}
echo json_encode($parents);
