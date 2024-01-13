<?php

return [

    'dashboard' => [
        'welcome' => 'Bonjour :name. Bienvenue sur Starter CMS.',
        'last_connection' => 'Votre dernière connexion date de: :date.',
        'last_users_logged_in' => 'Les derniers utilisateurs connectés',
    ],
    'user' => [
        'update_success' => 'Utilisateur mis à jour avec succès.',
        'create_success' => 'Utilisateur créé avec succès.',
        'delete_success' => 'L\'utilisateur ":name" a été supprimé avec succès.',
        'delete_list_success' => ':number utilisateurs ont été supprimés avec succès.',
        'edit_not_auth' => 'Vous n\'êtes pas autorisé(e) à éditer des utilisateurs.',
        'delete_not_auth' => 'Vous n\'êtes pas autorisé(e) à supprimer des utilisateurs.',
        'edit_user_not_auth' => 'Vous n\'êtes pas autorisé(e) à éditer cet utilisateur.',
        'update_user_not_auth' => 'Vous n\'êtes pas autorisé(e) à mettre à jour cet utilisateur.',
        'delete_user_not_auth' => 'Vous n\'êtes pas autorisé(e) à supprimer cet utilisateur.',
        'delete_list_not_auth' => 'Vous n\'êtes pas autorisé(e) à supprimer cet utilisateur: :name',
        'alert_user_dependencies' => 'Vous ne pouvez pas supprimer l\'utilisateur: :name car il possède :number :dependencies. Veuillez modifier ces dépendances et essayer à nouveau.',
        'unknown_user' => 'Utilisateur inconnu.',
    ],
    'role' => [
        'create_success' => 'Rôle créé avec succès.',
        'update_success' => 'Rôle mis à jour avec succès.',
        'delete_success' => 'Le rôle ":name" a été supprimé avec succès.',
        'delete_list_success' => ':number rôles ont été supprimés avec succès.',
        'edit_not_auth' => 'Vous n\'êtes pas autorisé(e) à éditer des rôles.',
        'delete_not_auth' => 'Vous n\'êtes pas autorisé(e) à supprimer des rôles.',
        'cannot_update_default_roles' => 'Vous ne pouvez pas modifier les rôles par défaut.',
        'cannot_delete_default_roles' => 'Vous ne pouvez pas supprimer les rôles par défaut.',
        'permission_not_auth' => 'Une ou plusieurs permissions sélectionnées ne sont pas autorisées.',
        'users_assigned_to_roles' => 'Un ou plusieurs utilisateurs sont assignés à ce rôle: :name',
        'cannot_delete_roles' => 'Les rôles suivants ne peuvent être supprimer: :roles',
    ],
    'permission' => [
        'role_does_not_exist' => 'Ce rôle :name n\'existe pas.',
        'invalid_permission_names' => 'Les permissions names: :names sont invalides.',
        'build_success' => ':number les permissions ont été construites avec succès.',
        'rebuild_success' => ':number permissions ont été reconstruites avec succès.',
        'no_new_permissions' => 'Aucune nouvelles permissions n\'ont été construites.',
        'missing_alert' => '(manquant !)',
    ],
    'group' => [
        'create_success' => 'Groupe créé avec succès.',
        'update_success' => 'Groupe mis à jour avec succès.',
        'delete_success' => 'Le groupe ":name" a été supprimé avec succès.',
        'delete_list_success' => ':number groupes ont été supprimés avec succès.',
        'edit_not_auth' => 'Vous n\'êtes pas autorisé(e) à éditer des groupes.',
        'delete_not_auth' => 'Vous n\'êtes pas autorisé(e) à supprimer des groupes.',
    ],
    'post' => [
        'create_success' => 'Post créé avec succès.',
        'update_success' => 'Post mis à jour avec succès.',
        'delete_success' => 'Le post ":title" a été supprimé avec succès.',
        'delete_list_success' => ':number posts ont été supprimés avec succès.',
        'edit_not_auth' => 'Vous n\'êtes pas autorisé(e) à éditer des posts.',
        'delete_not_auth' => 'Vous n\'êtes pas autorisé(e) à supprimer des posts.',
        'publish_list_success' => ':number posts ont été publiés avec succès.',
        'unpublish_list_success' => ':number posts ont été dépubliés avec succès.',
        'create_comment_success' => 'Commentaire créé avec succès.',
        'update_comment_success' => 'Commentaire mis à jour avec succès.',
        'delete_comment_success' => 'Commentaire supprimé avec succès.',
        'edit_comment_not_auth' => 'Vous n\'êtes pas autorisé(e) à éditer ce commentaire.',
        'delete_comment_not_auth' => 'Vous n\'êtes pas autorisé(e) à supprimer ce commentaire.',
        'comments_authentication_required' => 'Vous devez vous authentifier pour poster un commentaire.',
    ],
    'category' => [
        'create_success' => 'Catégorie créée avec succès.',
        'update_success' => 'Catégorie mise à jour avec succès.',
        'delete_success' => 'La catégorie ":name" a été supprimée avec succès.',
        'change_status_list_success' => 'Statuts de catégorie changés avec succès.',
        'no_subcategories' => 'Aucune sous-catégories',
    ],
    'email' => [
        'create_success' => 'Email créé avec succès.',
        'update_success' => 'Email mis à jour avec succès.',
        'delete_success' => 'L\'email ":name" a été supprimé avec succès.',
        'delete_list_success' => ':number emails ont été supprimés avec succès.',
        'edit_not_auth' => 'Vous n\'êtes pas autorisé(e) à éditer des emails.',
        'delete_not_auth' => 'Vous n\'êtes pas autorisé(e) à supprimer des emails.',
        'test_email_sending' => 'Un email de test est sur le point d\'être envoyé à :email.',
        'test_email_sending_ok' => 'L\'email a été envoyé avec succès',
        'test_email_sending_error' => 'L\'email n\'a pas pu être envoyé. Veuillez vérifier les logs et les paramètres d\'email puis essayer à nouveau.',
    ],
    'document' => [
        'create_success' => 'Document créé avec succès.',
        'delete_success' => 'Le document ":name" a été supprimé avec succès.',
        'no_document_to_delete' => 'Aucun document à supprimer.',
    ],
    'menu' => [
        'menu_not_found' => 'Le menu ayant le code: :code ne peut être trouvé.',
    ],
    'menuitem' => [
        'create_success' => 'Elément de menu créé avec succès.',
        'update_success' => 'Elément de menu mis à jour avec succès.',
        'delete_success' => 'Elément de menu :title supprimé avec succès.',
        'delete_list_success' => ':number éléments de menu ont été supprimés avec succès.',
        'change_status_list_success' => 'Statuts d\'élément de menu changés avec succès.',
    ],
    'search' => [
        'invalid_keyword_length' => 'Le mot clé doit comporter au moins :length caractères.',
        'no_matches_found' => 'Aucun résultat trouvé.',
    ],
    'message' => [
        'send_success' => 'Votre message a été envoyé avec succès.',
        'send_error' => 'Votre message n\'a pas pu être envoyé. Veuillez demander à l\administrateur de vérifier les logs et les paramètres d\'email.',
    ],
    'general' => [
        'update_success' => 'Paramètres sauvegardés avec succès.',
    ],
    'generic' => [
        'resource_not_found' => 'Resource non trouvé.',
        'access_not_auth' => 'Vous n\'êtes pas autorisé(e) à accéder à cette resource.',
        'edit_not_auth' => 'Vous n\'êtes pas autorisé(e) à éditer cette resource.',
        'create_not_auth' => 'Vous n\'êtes pas autorisé(e) à créer une resource.',
        'delete_not_auth' => 'Vous n\'êtes pas autorisé(e) à supprimer cette resource.',
        'change_status_not_auth' => 'Vous n\'êtes pas autorisé(e) à changer le statut de cette resource.',
        'change_order_not_auth' => 'Vous n\'êtes pas autorisé(e) à changer l\'ordre de cette resource.',
        'user_id_does_not_match' => 'L\'id de l\'utilisateur supposé éditer cet élément ne correspond pas à votre id. Ou peut être vous avez été déverrouillé par un administrateur.',
        'owner_not_valid' => 'Le propriétaire de l\'élément n\'est pas valide.',
        'no_item_selected' => 'Aucun élément sélectionné.',
        'mass_update_success' => ':number élément(s) mis à jour avec succès.',
        'mass_delete_success' => ':number élément(s) supprimés avec succès.',
        'check_in_success' => ':number éléments déverrouillés avec succès.',
        'check_in_not_auth' => 'Vous n\'êtes pas autorisé(e) à déverrouiller certains des éléments sélectionnés.',
        'mass_update_not_auth' => 'Vous n\'êtes pas autorisé(e) à mettre à jour certains des éléments sélectionnés.',
        'mass_delete_not_auth' => 'Vous n\'êtes pas autorisé(e) à supprimer certains des éléments sélectionnés.',
        'mass_publish_not_auth' => 'Vous n\'êtes pas autorisé(e) à publier certains des éléments sélectionnés.',
        'mass_unpublish_not_auth' => 'Vous n\'êtes pas autorisé(e) à dépublier certains des éléments sélectionnés.',
        'must_not_be_descendant' => 'Le noeud ne doit pas être un descendant.',
        'item_is_private' => 'L\'élément :name est privé.',
        'image_deleted' => 'Image supprimée avec succès.',
        'no_item_found' => 'Aucun élément n\'a été trouvé',
        'no_document_to_delete' => 'Aucun document à supprimer.',
        'can_no_longer_create_item' => 'Avertissement: L\'utilisateur ":name" est actuellement le propriétaire de cet élément. Toutefois, il n\'est plus autorisé à créer ce type d\'élément. Veuillez assigner cet élément à un autre utilisateur.',
        'form_errors' => 'Veuillez vérifier les erreurs dans le formulaire.',
        'cookie_info' => 'Ce site utilise des cookies pour offrir une meilleure expérience sur notre site web.',
        'checked_out' => 'Un utilisateur est déjà en train de consulter cet enregistrement. L\'enregistrement sera à nouveau consultable quand cet utilisateur aura terminé.',
    ],
    'js_messages' => [
        'confirm_item_deletion' => 'Un élément est sur le point d\'être supprimé. Etes vous sûr(e) ?',
        'confirm_multiple_item_deletion' => 'Un ou plusieurs éléments sont sur le point d\'être supprimé. Etes vous sûr(e) ?',
        'no_item_selected' => 'Aucun élément sélectionné.',
    ]
];
